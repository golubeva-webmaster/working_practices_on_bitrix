#!/bin/bash

array=( PATH/files-*.tgz )
count=${#array[@]}

if [ $1 -gt 0 ]
then
    ii=0
    while [ $count -ge $1 ]
    do
      rm -f ${array[$ii]}
      let ii=ii+1
      let count=count-1
    done
else
  if [ $count -gt 0 ]
  then
        rm -f ${array[0]}
  fi
fi

tar -C "/home/bitrix/XXXXXXXXXX" --exclude=./bitrix -czpf PATH/files-`date +%Y%m%d-%H%M%S`.tgz .

