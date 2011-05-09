from snep.call import *
from snep.entity import *
import events

class EvtHandler:

    def __init__(self, messenger):
        self._activeChannels = {}
        self._allocated = {}
        self._activeCalls = {}
        self._lastCallId = 0x0
        self.messenger = messenger

    def newChannel(self, channel):
        self._activeChannels[channel.id] = channel
        if channel.owner.__class__ is Exten:
            event = events.Status(events.Status.OFFHOOK)
            event.setEntity(channel.owner)
            self.messenger.broadcastEvent(event)

    def hangup(self, id):
        if id in self._activeChannels:
            if id not in self._allocated:
                self._lastCallId = self._lastCallId + 0x1
                call = Call(self._lastCallId)
                call.appendChannel(self._activeChannels[id])
                self._activeCalls[call.id] = call

            if self._activeChannels[id].owner.__class__ is Exten:
                event = events.Status(events.Status.ONHOOK)
                event.setEntity(self._activeChannels[id].owner)
                self.messenger.broadcastEvent(event)
            self._destroyChannel(id)

    def reportRelation(self, cid1, cid2):
        if cid1 in self._activeChannels and cid2 in self._activeChannels:
            if cid1 not in self._allocated and cid2 not in self._allocated:
                self._lastCallId = self._lastCallId + 0x1
                call = Call(self._lastCallId)
                call.appendChannel(self._activeChannels[cid1])
                call.appendChannel(self._activeChannels[cid2])
                call.link(cid1, cid2)
                self._activeCalls[self._lastCallId] = call

    def _destroyChannel(self, id):
        if id in self._activeChannels:
            del self._activeChannels[id]
            if id in self._allocated:
                del self._allocated[id]

    def list(self):
        print "list all the crazy shit"

    def dump(self):
        print("Calls:")
        for call in self._activeCalls:
            call = self._activeCalls[call]
            print("\tCall %x" % (call.id))
            channels = call.getChannels()
            
            for channel in channels:
                channel = channels[channel]
                print("\t\tChannel %x" % (channel.id))
