<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 网店配置模板
 *
 * 版本 $Id: config.sample.php 37482 2009-12-08 10:54:56Z ever $
 * 配置参数讨论专贴 http://www.shopex.cn/bbs/thread-61957-1-1.html
 */


// 数据库配置
define('DB_USER', 'usernamehere'); // 数据库用户名
define('DB_PASSWORD', 'yourpasswordhere'); // 数据库密码
define('DB_NAME', 'putyourdbnamehere'); // 数据库名
define('DB_HOST', 'localhost'); // 数据库HOST
define('DB_PREFIX', 'sdb_');
# define('DB_PCONNECT', 1); // 是否启用数据库持续连接？

// URL REWRITE配置
define('WITH_REWRITE', false);

# define('STORE_KEY', ''); // 密钥, 主要作用为
# define('LANG', 'zh_CN');
define('DEFAULT_TIMEZONE', '8');
define('WITHOUT_CACHE', false);

define('WITHOUT_KVSTORE_PERSISTENT', false);

// 以下为调优参数 
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');
define('DEBUG_JS',false); //是否开启javascript无压缩模式
define('DEBUG_CSS',false);//是否开启css无压缩模式
define('DEBUG_PHP',false);//是否开启php调试模式
define('EDITOR_ALL_SOUCECODE',false);//是否使后台可视化编辑器变为源码编辑模式
define('DONOTUSE_CSSFRAMEWORK',false);//是否禁用前台系统css框架
define('WITHOUT_AUTOPADDINGIMAGE',false);//图片处理时不需要自动补白

define('ROOT_DIR', realpath(dirname(__FILE__).'/../'));

// 二次开发目录设置,`custom`可以替换为自己的二次开发目录
# define('CUSTOM_CORE_DIR', ROOT_DIR.'/custom');

// 您可以更改这个目录的位置来获得更高的安全性
define('DATA_DIR', ROOT_DIR.'/data');
define('THEME_DIR', ROOT_DIR.'/themes');
define('PUBLIC_DIR', ROOT_DIR.'/public'); // 同一主机共享文件
define('MEDIA_DIR', PUBLIC_DIR.'/images');
define('SCRIPT_DIR', ROOT_DIR.'/script');
define('APP_DIR', ROOT_DIR.'/app');
# define('TMP_DIR', '/tmp');
# define('DEV_CHECKDEMO', true);

define('SECACHE_SIZE', '1g'); // 缓存大小,最大不能超过1G
# define('TEMPLATE_MODE','database');

define('SERVER_TIMEZONE', 8); // 服务器时区
# define('HTTP_PROXY', '127.0.0.1:8888');
@ini_set('memory_limit', '32M');
define('WITHOUT_GZIP', false);
define('WITHOUT_STRIP_HTML', true);

// 触屏版模板目录
define('WAP_THEME_DIR', ROOT_DIR.'/wap_themes');

// Session 配置
# define('SESS_NAME', 's'); // used as cookie name
# define('SESS_CACHE_EXPIRE', 60); // expires after n minutes

// 前台禁ip
# define('BLACKLIST', '10.0.0.0/24 192.168.0.1/24');

// 数据库集群.
# define('DB_SLAVE_NAME', DB_NAME);
# define('DB_SLAVE_USER', DB_USER);
# define('DB_SLAVE_PASSWORD', DB_PASSWORD);
# define('DB_SLAVE_HOST', DB_HOST);

// 确定服务器支持htaccess文件时，可以打开下面两个参数获得加速。
# define('GZIP_CSS',true);
# define('GZIP_JS',true);

// 日志 
# define('LOG_LEVEL', LOG_SYS_EMERG);
# define('LOG_LEVEL', LOG_SYS_ALERT);
# define('LOG_LEVEL', LOG_SYS_CRIT);
# define('LOG_LEVEL', LOG_SYS_ERR);
# define('LOG_LEVEL', LOG_SYS_WARNING);
# define('LOG_LEVEL', LOG_SYS_NOTICE);
# define('LOG_LEVEL', LOG_SYS_INFO);
# define('LOG_LEVEL', LOG_SYS_DEBUG);

// 日志保存类型 0=>使用系统日志， 3=>保存文件 
# define('LOG_TYPE', 0);
# define('LOG_TYPE', 3);

// 按日期分目录，每个ip一个日志文件。扩展名是php防止下载。
define('LOG_FILE', DATA_DIR.'/logs/site/{date}/{ip}.php');

// log文件头部放上exit()保证无法下载。
define('LOG_HEAD_TEXT', '<'.'?php exit()?'.">\n");
# define('LOG_FORMAT',"{gmt}\t{request}\t{code}");
# define('PAGE_CACHE_LOG', false);
define("MAIL_LOG", false);

// 禁止运行安装
# define('DISABLE_SYS_CALL', 1);

// 使用数据库存放改动过的模板
# define('THEME_STORAGE', 'db');

// syscache 后端存储处理类
# define('SYSCACHE_ADAPTER', 'base_syscache_adapter_chdb');
# define('SYSCACHE_ADAPTER', 'base_syscache_adapter_filesystem');

// kvstore 后台存储类
# define('KVSTORE_STORAGE', 'base_kvstore_filesystem');
# define('KVSTORE_STORAGE', 'base_kvstore_mysql');
# define('KVSTORE_STORAGE', 'base_kvstore_memcache');
# define('KVSTORE_STORAGE', 'base_kvstore_dba');
# define('KVSTORE_STORAGE', 'base_kvstore_tokyotyrant');
# define('KVSTORE_STORAGE', 'base_kvstore_mongodb');

// cache 后端存储类
# define('CACHE_STORAGE', 'base_cache_nocache');
# define('CACHE_STORAGE', 'base_cache_secache');
# define('CACHE_STORAGE', 'base_cache_memcache');
# define('CACHE_STORAGE', 'base_cache_memcached');

// kvstore memcache服务器配置
// socket  'unix:///tmp/memcached.sock'
// server  '127.0.0.1:11211'
// multi   'unix:///tmp/memcached.sock,127.0.0.1:11211,127.0.0.1:11212'
# define('KVSTORE_MEMCACHE_CONFIG', 'unix:///tmp/memcached.sock');

// cache memcache 服务器配置
// socket  'unix:///tmp/memcached.sock'
// server  '127.0.0.1:11211'
// multi   'unix:///tmp/memcached.sock,127.0.0.1:11211,127.0.0.1:11212'
# define('CACHE_MEMCACHE_CONFIG', 'unix:///tmp/memcached.sock');

// mongodb 服务器配置
// server:
// "mongodb://${username}:${password}@localhost" , "mongodb:///tmp/mongo-27017.sock"
# define('MONGODB_SERVER_CONFIG', 'mongodb://localhost:27017');
// multi mongo副本集集群配置方式下连接多个mongodb服务器写法
// 'mongodb://127.0.0.1:27017,127.0.0.1:27018,127.0.0.1:27019'  
// option:
// array("connect" => TRUE),array("username"=>'xxxx', "password"=>'xxx');
# define('MONGODB_OPTION_CONFIG','return '. var_export(array('connect'=>true),1).';');

// KV_PREFIX KV引擎前缀
define('KV_PREFIX', 'default');

// file_storage
# define('FILE_STORAGER', 'filesystem');
# define('STORAGE_MEMCACHED', '192.168.0.230:11211,192.168.0.231:11211');
# define('HOST_MIRRORS', 'http://img0.example.com, http://img2.example.com, http://img2.example.com');

// sphinx 配置
# define('SPHINX_SERVER_HOST', '127.0.0.1:9306');
# define('SPHINX_PCONNECT', 1); // 是否启用sphinx持续连接？

// scws 词典目录 编码默认为utf8
// 如果是集群部署，词典路径需一致，或者词典放在同步目录里面调用
# define('SCWS_DICT', '/usr/local/scws/etc/dict.utf8.xdb');
# define('SCWS_RULE', '/usr/local/scws/etc/rules.utf8.ini');

// app statics host
# define('APP_STATICS_HOST', 'http://img.demo.cn;http://img1.demo.cn');


/**************** compat functions begin ****************/


/**************** compat functions end ****************/
