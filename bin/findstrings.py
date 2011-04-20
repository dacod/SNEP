#! /usr/bin/python
# -*- coding: utf-8 -*-

# This app is designed to find all the translatable strings in the snep code and
# generate a .po for translation.
# This uses xgettext for php parsing and implements parsing of xml files.

__author__="Henrique Grolli Bassotto <henrique@opens.com.br>"
__date__ ="$20/04/2011 10:44:53$"

import sys
import getopt
import os

from xml.etree.ElementTree import ElementTree

def usage():
    print("usage: %s -s source_root_dir" % sys.argv[0])

def parse(file):
    """Parses XML files for what we need"""
    print("Parsing %s" % file)
    file = os.path.realpath(file)

    tree = ElementTree()
    tree.parse(file)

    def _parse(element):
        for child in element:
            if child.text != None and len(child.text.strip()) > 0:
                print child.text
            if "label" in child.attrib:
                print child.attrib['label']
            _parse(child)

    _parse(tree.getroot())
    

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "s:", ["source="])
    except getopt.GetoptError, err:
        # print help information and exit:
        print str(err) # will print something like "option -a not recognized"
        usage()
        sys.exit(2)

    root_dir = None

    for o, a in opts:
        if o in ("-s", "--source"):
            if not os.path.isdir(a):
                usage()
                sys.exit(1)
            else:
                root_dir = a
        else:
            assert False, "unhandled option"

    if root_dir == None:
        usage()
        sys.exit(1)

    print "Walking %s and searching for .xml files" % root_dir
    for root, dirs, files in os.walk(root_dir):
        for file in files:
            if file[-4:] == ".xml":
                parse("%s/%s" % (root.strip('/'), file))

if __name__ == '__main__':
    main()
