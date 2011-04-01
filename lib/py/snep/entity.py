__author__="guax"
__date__ ="$25/03/2011 10:59:46$"

__all__ = ["Trunk", "Exten"]

class Entity:
    def __init__(self):
        self.id = None
        self.interface = None
        self.callerid = "unknown"

class Trunk (Entity):
    ""

class Exten (Entity):
    class status:
        AVAILABLE = "available"
        UNAVAILABLE = "unavailable"
        BUSY = "busy"
        PAUSED = "paused"
