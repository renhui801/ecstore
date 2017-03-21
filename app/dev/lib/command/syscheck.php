<?php
class dev_command_syscheck extends base_shell_prototype{

    var $command_allcheck = "系统环境检测";
    var $command_allcheck_options = array(
        'library'=>array('title'=>'函数及扩展库','short'=>'l'),
        'database'=>array('title'=>'mysql数据库','short'=>'d'),
        'harddiskspace'=>array('title'=>'硬盘空间','short'=>'h'),
        'wpermissions'=>array('title'=>'目录和文件的读取权限','short'=>'w'),
        'network'=>array('title'=>'网络','short'=>'n'),
    );
    function command_allcheck(){
        $args = func_get_args(); 
        $options = $this->get_options(); 
        if($options){
            foreach($options as $key=>$val){
                if($val){
                    echo "检测".$this->command_allcheck_options[$key]['title'].":\n";
                    $this->$key();
                    echo "\n\n";
                }
            } 
        }else{
            $options = $this->command_allcheck_options;
            foreach($options as $key=>$val){
                echo "检测".$this->command_allcheck_options[$key]['title'].":\n";
                $this->$key();
                echo "\n\n";
            }
        }
    }


    function library(){
        $service = kernel::single('base_system_service');
        $deploy = base_setup_config::deploy_info();
        $library = $deploy['installer']['check']['extension_library'];
        $show_name = $deploy['installer']['check']['extension_library']['show_name'] ;
        $result = $service->check_extension_library($library,$show_name);
        echo "  ".$show_name."检测结果： \n";
        foreach($result as $key=>$val){
            if($val['result'] == 'true'){
                echo "     ".$val['value'].";"; 
            }else{
                echo "ERROR:".$val['value']."; ";
            }
        }
        echo "\n\n";
        $funtion = $deploy['installer']['check']['common_function'];
        $show_name = $deploy['installer']['check']['common_function']['show_name'] ;
        echo "  ".$show_name."检测结果： \n";
        $result = $service->check_common_function($funtion,$show_name);
        foreach($result['ifopen'] as $key=>$val){
            echo "     ".$val['value']."\n"; 
        }
        foreach($result['ifavailable'] as $key=>$val){
            echo "     ".$val['value']."\n"; 
        }

    }

    function database(){
        //检测mysql函数库是否可用
        $rst = function_exists('mysql_connect') && function_exists('mysql_get_server_info');
        echo  $rst ? app::get('dev')->_("  MySQL函数库可用...")."\n" : app::get('dev')->_('MySQL函数库未安装...')."\n";    

        //检测mysql数据库连接
        if(!$rst){
            echo app::get('dev')->_("  MySQL函数库连接出错...");
        }else{
            $rst = false;
            if(defined('DB_HOST')){
                if(defined('DB_PASSWORD')){
                    $rs = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
                }elseif(defined('DB_USER')){
                    $rs = mysql_connect(DB_HOST,DB_USER);
                }else{
                    $rs = mysql_connect(DB_HOST);
                }
                $db_ver = mysql_get_server_info($rs);
            }else{
                $sock = get_cfg_var('mysql.default_socket');
                if(PHP_OS!='WINNT' && file_exists($sock) && is_writable($sock)){
                    define('DB_HOST',$sock);
                }else{
                    $host = ini_get('mysql.default_host');
                    $port = ini_get('mysql.default_port');
                    if(!$host)$host = '127.0.0.1';
                    if(!$port)$port = 3306;
                    define('DB_HOST',$host.':'.$port);
                }
            }
            if(!$db_ver){
                if(substr(DB_HOST,0,1)=='/'){
                    $fp = @fsockopen("unix://".DB_HOST);
                }else{
                    if($p = strrpos(DB_HOST,':')){
                        $port = substr(DB_HOST,$p+1);
                        $host = substr(DB_HOST,0,$p);
                    }else{
                        $port = 3306;
                        $host = DB_HOST;
                    }
                    $fp = @fsockopen("tcp://".$host, $port, $errno, $errstr,2);
                }
                if (!$fp){
                    $db_ver = '无法连接';
                } else {
                    fwrite($fp, "\n");
                    $db_ver = fread($fp, 20);
                    fclose($fp);
                    if(preg_match('/([2-8]\.[0-9\.]+)/',$db_ver,$match)){
                        $db_ver = $match[1];
                        $rst = version_compare($db_ver,'4.0','>=');
                    }else{
                        $db_ver = '无法识别';
                    }
                }
            }else{
                $rst = version_compare($db_ver,'4.1','>=');
            }
            if($db_ver == '无法连接'){
                $error_msg = '  Mysql数据库无法连接...';
            }elseif($db_ver == '无法识别'){
                $error_msg = '  Mysql数据库版本无法识别...';
            }else{
                $error_msg = '  Mysql数据库版本是'.$db_ver.'，如果版本过低,请使用高于4.1的版本...';
            }
            echo app::get('dev')->_($error_msg)."\n";
        }  

        //检测mysql数据库是否可写可读
        $db = kernel::database();
        $db->exec('drop table if exists sdb_test')."\n";
        $sql_c = "CREATE TABLE `sdb_test` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`test` char(10) NOT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8
            ";
        if(@$db->exec($sql_c)){
            echo app::get('dev')->_("  Mysql创建测试表正常...")."\n";
        }else{
            echo app::get('dev')->_("  Mysql创建测试表不正常...")."\n";
        };
        $sql_i = "insert  into `sdb_test`(`id`,`test`) values (1,'test')";
        if(@$db->exec($sql_i)){
            echo app::get('dev')->_("  Mysql插入数据正常...")."\n";
        }else{
            echo app::get('dev')->_("  Mysql插入数据不正常...")."\n";
        };
        $sql_s = "select * from sdb_test";
        if(@$db->exec($sql_s)){
            echo app::get('dev')->_("  Mysql读取数据正常...")."\n";
        }else{
            echo app::get('dev')->_("  Mysql读取数据不正常...")."\n";
        };
        $db->exec('drop table if exists sdb_test')."\n";
    }

    function harddiskspace(){
        $file = "/tmp/test.txt";
        if(!file_exists($file)){
           $fp = fopen($file,'w+');
           if(!$fp){
                echo "/tmp目录写入失败，请查看tmp目录的权限或者查看服务器硬盘空间是否足够";
           }else{
                echo "服务器硬盘空间足够";
           }
        }
    }

    function wpermissions(){
        //检测ecstore系统目录是否可写 
        $deploy = base_setup_config::deploy_info();
        $writeable_dir = $deploy['installer']['writeable_check']['dir'];
        $unablewrite = array();
        if(is_array($writeable_dir)){
            foreach($writeable_dir AS $dir){
                $file = ROOT_DIR . '/' . $dir . '/test.html';
                if($fp = @fopen($file, 'w')){
                    @fclose($fp);
                    @unlink($file);                   
                }else{
                    $unablewrite[] = $dir;
                }
            }
        }          

        //检测系统tmp目录是否可写
        $tmpfile="/tmp/test.html";
        if($tmpfp = @fopen($tmpfile,'w')){
            @fclose($tmpfp);
            @unlink($tmpfile);
        }else{
            $unablewrite[] = "/tmp";
        }

        if(count($unablewrite)){
            echo "  ".join(',', $unablewrite) . app::get('dev')->_('  目录不可写,请检测硬盘空间是否足够或者目录权限web是否用户可以写入...')."\n";
        }else{
            echo app::get('dev')->_('  目录可写性检测通过...')."\n";
        }

        //检测kvstore的存取权限
        @$this->app->setConf('dev.test.data','testdata');
        $s = @$this->app->getConf('dev.test.data');
        if(!empty($s)){
            $rst = true;
            @$this->app->setConf('dev.test.data','');
        }else{
            $rst = false;
        }
        echo $rst ? app::get('dev')->_("  kvstore存取正常...")."\n" : app::get('dev')->_("  kvstore存取不正常,请检测data/kvstore目录中的权限...")."\n";


    }

    function network(){

        //检测dns
        //service.ecos.shopex.cn
        $fp = fsockopen("service.shopex.cn", 80, $errno, $errstr, 30);
        if (!$fp) {
            echo "  fsockopen请求中心地址不通，请检测服务器dns \n";
        } else {
            $out = "GET / HTTP/1.1\r\n";
            $out .= "Host: service.shopex.cn\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            $data = "";
            while (!feof($fp)) {
                $data .= fgets($fp, 128);
            }
            fclose($fp);
            echo "  fsockoprn dns检测通过\n";
        }
    }
}
