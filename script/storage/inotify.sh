#!/bin/bash  

source $(cd `dirname $0`; pwd)/ttserver.sh

src=$root/public
inotifywait="/usr/local/bin/inotifywait"

#执行监控函数
function inotify(){
    $inotifywait -mrq --exclude '/*/LC_MESSAGES/' --format '%w%f %e' -e modify,delete,create,move $src | while read file command;do
        case $command in
            MODIFY|CREATE|MOVED_TO)
                create "$file" &
                ;;
            DELETE|MOVED_FROM)
                delete "$file" &
                ;;
        esac
    done
}

#如果没有传参则默认为开启监控
if [ x$1 = x ]
then
    echo $(cd `dirname $0`; pwd)/inotify.sh '[start|stop|restart]'
    exit
fi

#监控开状态修改
case $1 in
    start)
        inotify &
        echo '开始监控'
        ;;
    restart)
        pkill inotifywait 
        echo '结束监控'
        inotify &
        echo '开始监控'
        ;;
    stop)
        pkill inotifywait
        echo '结束监控'
        ;;
esac
