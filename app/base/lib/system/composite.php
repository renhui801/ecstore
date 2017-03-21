<?php

class base_system_composite {
    function check_php_version($list,$show_name){
        $name = $list['name'];
        $value = $list['value'];
        $sign = $list['sign'];
        $now_vers =  PHP_VERSION;
        $result = 'false';
        if($sign == ">=") $s="大于";
        else $s="小于";
        if("$now_vers $sign $value"){
            $result = 'true';
        }
        return array(
                'value'=>sprintf('%s要求的%s为%s %s，现为%s',$name,$show_name,$s,$value,$now_vers),
                'result'=>$result,
            );
    }


    function check_mysql_version($list,$show_name){
        $name = $list['name'];
        $value = $list['value'];
        $sign = $list['sign'];
        $result = 'true';
        if($sign == ">=") $s="大于";
        else $s="小于";
        $mysql_now_vers = mysql_get_client_info();

        if(function_exists('mysql_connect') && function_exists('mysql_get_server_info')){
            $result = 'true';
        } 

        return array(
                'value'=>sprintf('%s'.($result?"函数库可用":"函数库不可用").',要求%s为%s,'.($result?"现为%s":""),$name,$show_name,$value,$mysql_now_vers),
                'result'=>$result,
            );

    }

    function check_file_flock($list,$show_name){
        $tmpfname = tempnam(ROOT_DIR . "/data/cache", "foo");
        $handle = fopen($tmpfname, "w");
        $rst = flock($handle,LOCK_EX);
        fclose($handle);
        unlink($tmpfname);
        //'支持文件锁(flock)']
        return array(
            'value'=>$rst?'支持文件锁(flock)':'您不支持文件锁(flock)',
            'result'=>$rst?'true':'false',
        ); 

    }

    function check_web_ping($list,$show_name){

        $content = '';
        $fp = fsockopen("service.shopex.cn", 80, $errno, $errstr, 30);
        if (!$fp) {
            $result = 'false';
        } else {
            $out = "GET / HTTP/1.1\r\n";
            $out .= "Host: service.shopex.cn\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            while (!feof($fp)) {
                $content .= fgets($fp, 128);
            }
            fclose($fp);
            $result = 'true';
        }
        return array(
            'value'=>($result=='true')?$list['name']."函数使用正常":"此服务器fsockopen使用异常，请检测该服务器网络",
            'result'=>$result,
            );

    }


    function check_web_pcntl($list,$show_name){
        $name = $list['name']; 
        $result = false;
        if(!ini_get('safe_mode') && PATH_SEPARATOR==":"){
            $rs = function_exists('system');
            $result = true;
            if($rs){
                $result_s = system('php -m |grep "pcntl"',$return);
                if('pcntl' == $result_s && $return == 0){
                    @unlink($file);
                }else{
                    $result = false;
                }
                return array(
                    'value'=>sprintf('%s%s,%s',$name,$show_name,($result)?'OK':'未安装'),
                    'result'=>$result?'true':'false',
                );
            }
        }
    }

}
