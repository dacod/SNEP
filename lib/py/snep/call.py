__author__="guax"
__date__ ="$25/03/2011 10:59:46$"
__all__ = ["Channel", "Link", "Call"]

class Channel:

    def __init__(self, id):
        self.id = id
        self.owner = None

    def __str__(self):
        return "%x" % (self.id)

class Link:

    def __init__(self, channel1, channel2):
        self.channel1 = channel1
        self.channel2 = channel2

class Call:

    def __init__(self, id):
        self.id = id
        self._channels = {}
        self._links = []

    def appendChannel(self, channel):
        if channel.id not in self._channels:
            self._channels[channel.id] = channel

    def link(self, cid1, cid2):
        self._links.append(Link(cid1, cid2))

    def getChannels(self):
        return self._channels
