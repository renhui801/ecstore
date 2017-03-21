<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class base_application_prototype_filepath extends base_application_prototype_content{

    var $current;
    var $path;
    /**
     *安装的目录是否在public目录中，false( app/目录中 ) | true (public/app/目录中)
     *lang 语言包放在 public/app/appid/lang 因此在安装lang的时候(base_application_lang)，定义$path_ispublic = true
     */
    var $path_ispublic = false;

    function init_iterator(){
        $tmp_core_dir = '';
        if( is_dir($this->target_app->app_dir.'/'.$this->path) ){
            $tmp_core_dir =  $this->target_app->app_dir.'/'.$this->path;
        }
        if( $this->path_ispublic && is_dir($this->target_app->public_app_dir.'/'.$this->path) ){
            $tmp_core_dir =  $this->target_app->public_app_dir.'/'.$this->path;
        }

        if($tmp_core_dir){
            $cong_files = array();
            if(defined('CUSTOM_CORE_DIR') && is_dir(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path)){
                $custom_core_dir = new DirectoryIterator(CUSTOM_CORE_DIR.'/'.$this->target_app->app_id.'/'.$this->path); #从
                $custom_core_dir = new NewCallbackFilterIterator($custom_core_dir, function($current){
                    return  ! $current->isDot() ;
                });
                foreach($custom_core_dir as $v){
                    $cong_files[] = $v->getFilename();
                }
            }
            $core_dir = new DirectoryIterator($tmp_core_dir);#主
            $core_dir = new NewCallbackFilterIterator($core_dir, function($current, $key, $iterator, $params){
                if(!$current->isDot() && !in_array($current->getFilename(),$params)){
                  return $current;
                }
            }, $cong_files);

            $iterator = new AppendIterator;
            if($custom_core_dir){
                $iterator->append($custom_core_dir);
            }
            $iterator->append($core_dir);

            return $iterator;
        }else{
            return new ArrayIterator(array());
        }
    }

    public function getPathname(){
        return $this->iterator()->getPathname();
    }

    public function current() {
        $this->key = $this->iterator()->getFilename();
        return $this;
    }

    function prototype_filter(){
        $filename = $this->iterator()->getFilename();
        if($filename{0}=='.'){
            return false;
        }else{
            return $this->filter();
        }
    }
    
    function last_modified($app_id){
        $info_arr = array();
        foreach($this->detect($app_id) as $item){
            //$modified = max($modified,filemtime($this->getPathname()));
            //todo: md5
            $filename = $this->getPathname();
            if(is_dir($filename)){
                foreach(utils::tree($filename) AS $k=>$v){
					if (is_dir($v)) continue;
                    $info_arr[$v] = md5_file($v);
                }
            }else{
                $info_arr[$filename] = md5_file($filename);
            }
        }
        ksort($info_arr);
        return md5(serialize($info_arr));
    }



}
