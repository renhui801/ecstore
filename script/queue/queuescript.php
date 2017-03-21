#!/usr/bin/env php
<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

error_reporting(E_ALL ^ E_NOTICE);
set_time_limit(0);

$root_dir = realpath(dirname(__FILE__).'/../../');
$script_dir = $root_dir.'/script';

// 修改默认的config配置

define('LOG_LEVEL', LOG_INFO);
define('LOG_TYPE', 3);
define('LOG_FILE', $root_dir.'/data/logs/queue/{date}.php');

//-------------------------------------------------------------------------------------
require_once($script_dir."/lib/runtime.php");
set_error_handler('error_handler');
//-------------------------------------------------------------------------------------
if (!isset($argv[1])) {
    echo "Hey boy or girl, Please give me the queue name!\n";
    exit;
 }
$queue_name = $argv[1];

if($queue_message = system_queue::instance()->get($queue_name)){
    system_queue::instance()->run_task($queue_message);
    //echo $queue_message->get_id();
    system_queue::instance()->ack($queue_message);

}
//-------------------------------------------------------------------------------------

function error_handler($code,$msg,$file,$line){
    if($code == ($code & (E_ERROR ^ E_USER_ERROR ^ E_USER_WARNING))){
        logger::error(sprintf('ERROR:%d @ %s @ file:%s @ line:%d', $code, $msg, $file, $line));
        if($code == ($code & (E_ERROR ^ E_USER_ERROR))){
            exit;
        }
    }
    return true;
}

