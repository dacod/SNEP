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
    file = os.path.realpath(file)

    tree = ElementTree()
    tree.parse(file)

    def _parse(element):
        for child in element:
            if child.text != None and len(child.text.strip()) > 0:
                print("#: %s" % file)
                print('msgid "%s"\nmsgstr ""' % child.text)
                print('')
            if "label" in child.attrib:
                print("#: %s" % file)
                print('msgid "%s"\nmsgstr ""' % child.attrib['label'])
                print('')
            _parse(child)

    _parse(tree.getroot())
    

def main():
    try:
        opts, args = getopt.getopt(sys.argv[1:], "s:f:", ["source=","file="])
    except getopt.GetoptError, err:
        # print help information and exit:
        print str(err) # will print something like "option -a not recognized"
        usage()
        sys.exit(2)

    root_dir = None
    source_file = None

    for o, a in opts:
        if o in ("-s", "--source"):
            if not os.path.isdir(a):
                usage()
                sys.exit(1)
            else:
                root_dir = a
        elif o in ("-f", "--file"):
            if not os.path.isfile(a):
                usage()
                sys.exit(1)
            else:
                source_file = a
        else:
            assert False, "unhandled option"

    if root_dir == None and source_file == None:
        usage()
        sys.exit(1)

    if root_dir != None:
        for root, dirs, files in os.walk(root_dir):
            for file in files:
                if file[-4:] == ".xml":
                    parse("%s/%s" % (root.strip('/'), file))
    else:
        parse("%s" % source_file)

if __name__ == '__main__':
    main()
