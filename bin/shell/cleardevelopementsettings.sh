#!/bin/sh

INIPATH="settings/*.ini"
BACKUPPATH="settings/backup_$(date +'%Y%m%d-%H%M')"
TEMPFILE="settings/tmp.out"

mkdir $BACKUPPATH

for f in $INIPATH
do
  if [ -f $f ]; then
    cp -f $f $BACKUPPATH
	sed -e '/^\(.*\)##!$/d' $f > $TEMPFILE && mv $TEMPFILE $f
	sed -e 's/^#!\(.*\)$/\1/g' $f > $TEMPFILE && mv $TEMPFILE $f
  else
   echo "Error: Cannot read $f"
  fi
done

echo "Old files have been saved in $BACKUPPATH"

if [ -f $TEMPFILE ]; then
	rm $TEMPFILE
fi