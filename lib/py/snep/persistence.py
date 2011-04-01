__author__="guax"
__date__ ="$28/03/2011 14:20:34$"

__all__ = ["Extensions", "Trunks"]

import re
import MySQLdb
from _mysql_exceptions import OperationalError
from snep import config
from snep.entity import *

class DB:
    conn = None

    user = ""
    passwd = ""
    db = ""

    def __init__(self, user, passwd, db):
        self.user = user
        self.passwd = passwd
        self.db = db

    def connect(self):
        self.conn = MySQLdb.connect(user=self.user,passwd=self.passwd,db=self.db)

    def cursor(self):
        try:
            cursor = self.conn.cursor()
            # ridiculous error check for terrible python dbapi
            cursor.execute("SELECT 1")
            cursor.fetchall()
            return cursor
        except (AttributeError, MySQLdb.OperationalError):
            self.connect()
            return self.conn.cursor()

class Extensions:
    db = None

    def get(self, id):
        print "Get exten %s" % id

    def _assemble(self, info):
        exten = Exten()
        exten.id = info['id']
        exten.callerid = info['callerid']
        return exten

    def findOwner(self, interface):
        c = Extensions.db.cursor()
        c.execute("SELECT name, canal, callerid FROM peers WHERE name != 'admin' AND peer_type='R'")

        haveData = True
        while haveData:
            peer = c.fetchone()
            if peer == None:
                haveData = False
            else:
                if interface.lower() == peer[1].lower():
                    return self._assemble({'id':peer[0],'callerid':peer[2]})
        return None

class Trunks:
    db = None

    def get(self, id):
        print "Get trunk %s" % id

    def _assemble(self, info):
        trunk = Trunk()
        trunk.id = info['id']
        return trunk

    def findOwner(self, interface):
        c = Trunks.db.cursor()
        c.execute("SELECT id, id_regex FROM trunks")

        haveData = True
        while haveData:
            trunk = c.fetchone()
            if trunk == None:
                haveData = False
            else:
                if re.match(trunk[1], interface, re.IGNORECASE):
                    return self._assemble({'id':trunk[0]})
                
        return None

db = DB(config.get('ambiente','db.username'), config.get('ambiente','db.password'), config.get('ambiente','db.dbname'))
Extensions.db = db
Trunks.db = db

def findOwner(interface):
    t = Trunks()
    trunk = t.findOwner(interface)
    
    if trunk is not None:
        return trunk
    else:
        e = Extensions()
        return e.findOwner(interface)
