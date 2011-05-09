__author__ ="guax"
__date__ ="$28/03/2011 09:54:48$"

__all__ = ["Status"]

from snep.entity import *

class Event:
    __event__ = "unknown"

    def __init__(self):
        self.data = {"event": self.__event__}

    def getData(self):
        return self.data

class Status(Event):
    __event__ = "snep.exten.status"
    UP = "up"
    DOWN = "down"
    OFFHOOK = "offhook"
    ONHOOK = "onhook"
    PAUSE = "pause"
    UNPAUSE = "unpause"

    _meaning = {
        UP : Exten.status.AVAILABLE,
        DOWN : Exten.status.UNAVAILABLE,
        OFFHOOK : Exten.status.BUSY,
        ONHOOK : Exten.status.AVAILABLE,
        PAUSE : Exten.status.PAUSED,
        UNPAUSE : Exten.status.AVAILABLE
    }

    def __init__(self, operation):
        Event.__init__(self)
        self.data['operation'] = operation

    def setOperation(self, operation):
        self.data['operation'] = operation

    def setEntity(self, exten):
        self.data['exten'] = exten.id

    def getData(self):
        self.data['status'] = self._meaning[self.data['operation']]
        return self.data
