<?php

ob_implicit_flush(1);
$root_dir = realpath(dirname(__FILE__).'/../../');

require($root_dir."/config/config.php");

require(APP_DIR.'/base/kernel.php');
@require(APP_DIR.'/base/defined.php');

if(!kernel::register_autoload()){
    require(APP_DIR.'/base/autoload.php');
}
cachemgr::init(false);

// 时区设置
date_default_timezone_set(
    defined('DEFAULT_TIMEZONE') ? ('Etc/GMT'.(DEFAULT_TIMEZONE>=0?(DEFAULT_TIMEZONE*-1):'+'.(DEFAULT_TIMEZONE*-1))):'UTC'
);

if (!defined('BASE_URL')) {
    if ($shell_base_url = app::get('base')->getConf('shell_base_url')) {
        define('BASE_URL', $shell_base_url);
    }else{
        echo 'Please install ecstore first, and login to the backend ';
    }
 }


