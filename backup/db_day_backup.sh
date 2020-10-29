#!/bin/bash

array=( PATH/db-*.tgz )
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

tar -C "/var/lib/mysql" --exclude=./bitrix -czpf PATH/db-files_var_lib_mysql`date +%Y%m%d-%H%M%S`.tgz .
mysqldump -uUSERNAME_HERE -pPASSWD_HERE DB_NAME > PATH/db-mysqldump-`date +%Y%m%d-%H%M%S`.sql