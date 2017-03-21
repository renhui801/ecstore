#!/bin/bash

#storage存储服务器地址
host=192.168.65.144
#ttserver端口
port=1978
root=$(cd `dirname $0`; cd ../../; pwd)

#$1传入为绝对路径
function create()
{
    file=${1/$root/''}
    curl -T "$root$file" http://$host:$port/$file 1>/dev/null 2>&1
    if [ x$? != x0 ]
    then
        echo 'ERROR ' `date`' 执行 curl -T '$root$file' http://'$host:$port/$file >>$root/data/logs/ttserver.log;
        echo '执行脚本发生错误：查看 '$root'/data/logs/ttserver.log 错误日志';
        if [ x$2 != x ]
        then
            kill -9 $2
        fi
        exit;
    fi
    #echo "save" "http://$host:$port/$file"
}

function delete()
{
    #curl -X DELETE http://192.168.65.144:1978//images.png
    file=${1/$root/''}
    curl -X DELETE http://$host:$port/$file 1>/dev/null 2>&1
    if [ x$? != x0 ]
    then
        echo 'ERROR ' `date`' 执行 curl -X DELETE http://'$host:$port/$file >>$root/data/logs/ttserver.log;
        echo '执行脚本发生错误：查看 '$root'/data/logs/ttserver.log 错误日志';
        if [ x$2 != x ]
        then
            kill -9 $2
        fi
        exit;
    fi
    #echo "delete" "http://$host:$port/$file"
}
