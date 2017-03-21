<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$constants = array(
        'DATA_DIR'=>ROOT_DIR.'/data',
        'TMP_DIR' => sys_get_temp_dir(),
        'SET_T_STR'=>0,
        'SET_T_INT'=>1,
        'SET_T_ENUM'=>2,
        'SET_T_BOOL'=>3,
        'SET_T_TXT'=>4,
        'SET_T_FILE'=>5,
        'SET_T_DIGITS'=>6,
        'LC_MESSAGES'=>6,
        'DEFAULT_TIMEZONE'=>8,
        'DEBUG_TEMPLETE'=>false, // todo
        'WITH_REWRITE'=>false,
        'PRINTER_FONTS'=>'', //打印字体
        'PHP_SELF'=>(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']),
        'LOG_TYPE'=>3,
        'DATABASE_OBJECT'=>'base_db_connections',
        'KVSTORE_STORAGE'=>'base_kvstore_filesystem',
        'CACHE_STORAGE'=>'base_cache_secache',
		'SHOP_USER_ENTERPRISE'=>'http://passport.shopex.cn/index.php',
		'SHOP_USER_ENTERPRISE_API'=>'http://passport.shopex.cn/api.php',
        'URL_APP_FETCH_INDEX'=>'http://get.ecos.shopex.cn/index.xml',
        'LICENSE_CENTER'=>'http://service.ecos.shopex.cn/openapi/api.php', //证书的正式外网地址.
        'LICENSE_CENTER_V'=>'http://service.shopex.cn',  //License授权输出图片流【tito】 请求地址
        'URL_APP_FETCH'=>'http://get.ecos.shopex.cn/%s/',
        'MATRIX_RELATION_URL' => 'http://www.matrix.ecos.shopex.cn/',
        'OPENID_URL' => 'http://openid.ecos.shopex.cn/redirect.php',
        "SHOPEX_STAT_WEBURL" => 'http://stats.shopex.cn/index.php',
        'LICENSE_CENTER_INFO'=>'http://service.shopex.cn/',
        'IMAGE_MAX_SIZE'=> 1024*1024,
        'KV_PREFIX' => 'defalut',
        'MATRIX_URL'=>'http://matrix.ecos.shopex.cn/async',
		'MATRIX_REALTIME_URL'=>'http://matrix.ecos.shopex.cn/sync',
		'MATRIX_SERVICE_URL'=>'http://matrix.ecos.shopex.cn/service',
        'MATRIX_GLOBAL' => 1,
        'MATRIX_REALTIME' => 2,
        'MATRIX_SERVICE' => 3,
        'AUTH_OPEN_URL' => 'http://auth.open.shopex.cn',

    );

foreach($constants as $k=>$v){
   if(!defined($k))define($k,$v);
}

