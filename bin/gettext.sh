#!/bin/sh

echo "" > messages.po
find $1 -type f -iname "*.php" | grep -v ".svn" | grep -v "Zend/" | xgettext --keyword=translate --from-code=utf-8 -j -f -;
export PYTHONIOENCODING=utf_8;
for file in `find $1 -type f -name *.xml | grep -v .svn | grep -v Zend/`; do ./trunk/bin/findstrings.py -f $file >> messages.po; done;