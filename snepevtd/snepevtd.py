#!/usr/bin/python -u
# -*- coding: utf-8 -*-
import logging
import os
from twisted.internet import reactor, protocol
from starpy import manager
# Fix for debian
try:
    import json
except ImportError:
    import simplejson as json

from evthandler import EvtHandler
from snep.call import *
from snep import config
config._file = '../includes/setup.conf'

from snep import persistence

logging.basicConfig()
logging.getLogger("").setLevel(logging.DEBUG)

amilog = logging.getLogger("AMI")
amilog.setLevel(logging.ERROR)

log = logging.getLogger("EVENTD")
log.setLevel(logging.DEBUG)

def set_proc_name(newname):
    from ctypes import cdll, byref, create_string_buffer
    libc = cdll.LoadLibrary('libc.so.6')
    buff = create_string_buffer(len(newname)+1)
    buff.value = newname
    libc.prctl(15, byref(buff), 0, 0, 0)

class MyAMIFactory(manager.AMIFactory):
    amiWorker = None
    def __init__(self, username, password, worker):
        amilog.debug("Inicializando MyAMIFactory...")
        self.amiWorker = worker
        manager.AMIFactory.__init__(self, username, password)

    def clientConnectionLost(self, connector, reason):
        amilog.error("Connection Lost, reason: %s" % reason.value)
        self.amiWorker.onLoseConnection(reason)
        reactor.callLater(10, self.amiWorker.connect)

    def clientConnectionFailed(self, connector, reason):
        amilog.error("Connection Lost, reason: %s" % reason.value)
        self.amiWorker.onLoseConnection(reason)
        reactor.callLater(10, self.amiWorker.connect)

class AMIWorker():
    connected  = False
    amiFactory = None
    ami        = None

    handler = None

    ami_host = None
    ami_port = None
    ami_user = None
    ami_pass = None

    astChannelMap = {}

    lastId = 0x0

    def __init__(self, hostname='127.0.0.1', port=5038, username='ami_user', password='ami_pass'):
        amilog.debug("Inicializando AMIWorker...")
        self.ami_host = hostname
        self.ami_port = port
        self.ami_user = username
        self.ami_pass = password
        self.handlers = {
            "Newchannel":self._newChannel,
            "Hangup":self._hangup
        }

        self.amiFactory = MyAMIFactory(self.ami_user, self.ami_pass, self)

        reactor.callLater(1, self.connect)

    def connect(self):
        amilog.debug("Tentando se conectar a %s:%s" % (self.ami_host, self.ami_port))
        d = self.amiFactory.login(self.ami_host, self.ami_port)
        d.addCallback(self.onLoginSuccess)
        d.addErrback(self.onLoginFailure)
        return d

    def onLoginSuccess(self, ami):
        amilog.info("Autenticacao bem sucedida...")
        self.ami       = ami
        self.connected = True
        for event, handler in self.handlers.items():
            self.ami.registerEvent(event, handler)
        log.info("Server Ready")

    def onLoginFailure(self, reason):
        amilog.error("Falha de Autenticacao: %s" % reason.value)
        self.connected = False

    def onLoseConnection(self, reason):
        amilog.error("Perda de Conexao: %s" % reason.value)
        self.connected = False

    def _newChannel(self, ami, event):
        if event['channel'].find(',') < 0:
            self.lastId = self.lastId + 0x1
            self.astChannelMap[event['channel']] = self.lastId
            channel = Channel(self.lastId)
            channel.owner = persistence.findOwner(event['channel'][0:event['channel'].index('-')])
            self.handler.newChannel(channel)
    
    def _hangup(self, ami, event):
        if event['channel'] in self.astChannelMap:
            self.handler.hangup(self.astChannelMap[event['channel']])
            del self.astChannelMap[event['channel']]

    def dump(self):
        self.handler.dump()
        
class TCPProtocol(protocol.Protocol):
    def connectionMade(self):
        log.info("Client Connected: %s" % self.transport.client[0])
        self.factory.clientProtocols.append(self)

    def sendEvent(self, event):
        self.transport.write(json.dumps(event.getData())+"\n")

    def dataReceived(self, data):
        valid = True
        response = {}

        try:
            raw_event = json.loads(data)
        except ValueError:
            response['event'] = "error"
            response['code'] = "403"
            response['message'] = "Bad Request. Invalid json object."
            valid = False
            self.transport.write(json.dumps(response) +  "\n")

        if valid:
            self.factory.eventHandler.list()

    def connectionLost(self, reason):
        log.info("Client Disconnected: %s" % self.transport.client[0])
        self.factory.clientProtocols.remove(self)

class ProtocolFactory(protocol.ServerFactory):

    protocol = TCPProtocol

    eventHandler = None

    def __init__(self):
        self.clientProtocols = []

    def broadcastEvent(self, event):
        for client in self.clientProtocols:
            client.sendEvent(event)

if __name__ == '__main__':
    os.chdir(os.path.dirname(__file__))
    set_proc_name("snepevtd")

    ami = AMIWorker(config.get('ambiente','ip_sock'), 5038, config.get('ambiente','user_sock'), config.get('ambiente','pass_sock'))

    messenger = ProtocolFactory()
    reactor.listenTCP(5030,messenger)
    ami.handler = EvtHandler(messenger)
    messenger.eventHandler = ami.handler

    reactor.run()
