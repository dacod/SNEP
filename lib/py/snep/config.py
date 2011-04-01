__author__="guax"
__date__ ="$28/03/2011 15:57:43$"

from ConfigParser import ConfigParser
import string
import os

_file = "/etc/snep.conf"
_config = ConfigParser()
_read = False

def get(section, key):
    global _read, _file, _config
    if _read == False:
        if os.path.exists(_file):
            _config.read(_file)
            _read = True
        else:
            raise IOError("Unable to read file %s" % _file)
    return string.replace(_config.get(section, key), '"', '')