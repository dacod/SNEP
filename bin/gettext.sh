#!/bin/sh

echo "" > messages.po
find $1 -type f \( -name "*.php" -o -name "*.phtml" \) | grep -v ".svn" | grep -v "Zend/" | xgettext --keyword=translate --language=PHP --from-code=utf-8 -j -f -;
echo "" >> messages.po;
export PYTHONIOENCODING=utf_8;
for file in `find $1 -type f -name *.xml | grep -v .svn | grep -v Zend/`; do ./findstrings.py -f $file >> messages.po; done;
xgettext -s messages.po