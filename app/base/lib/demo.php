<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class base_demo
{

    public function init()
    {
        $demo_dir = ROOT_DIR . '/demo';
        if(is_dir($demo_dir)){
            $handle = opendir($demo_dir);
            while($file = readdir($handle)){
                $realfile = $demo_dir . '/' . $file;
                if(is_file($realfile)){
                    list($app_id, $model, $ext) = explode('.', $file);
                    if($ext == 'sdf'){
                        $this->init_sdf($app_id, $model, $realfile);
                    }elseif($ext=='php' && $model=='setting'){
                        $setting = include($realfile);
                        $this->init_setting($app_id, $setting);
                    }
                }
            }
            closedir($handle);
        }
    }//End Function

    public function init_setting($app_id, $setting)
    {
        $app = app::get($app_id);
        if(is_array($setting)){
            foreach($setting AS $key=>$value){
                $app->setConf($key, $value);
            }
        }
    }//End Function

    public function init_sdf($app_id, $model, $file)
    {
        $handle = fopen($file, 'r');
        if($handle){
            while(!feof($handle)){
                $buffer .= fgets($handle);
                if(strtoupper(substr(PHP_OS,0,3))==='WIN' && $model=='goods'){   
                    $p = preg_match_all('/i:\d{8,12};/',$buffer,$out);   
                    if($p){   
                        foreach($out[0] as $val){    
                            $str = explode(':',$val);     
                            if($str[1] < 2147483647){     
                                continue;  
                            }
                            $num = strlen(strval($str[1]))-1;   
                            $s = explode(';',$str[1]);   
                            $pattern[] = '/'.$val.'/';  
                            $replacement[] = 's:'.$num.':"'.$s[0].'";';     
                        }    
                        $buffer = preg_replace($pattern,$replacement,$buffer);
                    } 
                }
                if(!($sdf = unserialize($buffer))){
                    continue;
                }
                app::get($app_id)->model($model)->db_save($sdf);
                $buffer = '';
            }
            fclose($handle);
        }
    }//End Function
}//End Class
