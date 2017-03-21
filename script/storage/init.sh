#!/bin/bash

source $(cd `dirname $0`; pwd)/ttserver.sh

if [ x$1 = x ]
then
    src=$root/public
else
    src=$1
fi

i=1
find $src  -type f | grep -v LC_MESSAGES | while read file;
do

    #父进程被子进程干掉，直接exit
    ps -p $$ >/dev/null
    if [ $? = 1 ]
    then
        exit;
    fi

    #创建
    create "$file" $$ &
    echo 'save' $file

    #并发100
    i=`expr $i + 1`;
    num=`expr $i % 100`
    if [ $num -eq 0 ]
    then
        echo '保存中。。。'
        wait
    fi
done

echo 'storage初始化数据成功';
