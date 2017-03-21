<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @package base
 * @author bryant@shopex.cn
 * @license
 *
 * setting-系统配置类 
 */


 
class base_setting{
    public $app;
    public $__source_file_path = null;
    private $__app_conf = array();
    private $__app_setting = array();

    /*
     * 获取setting配置原文件路径
     * @access private
     * @return string 原文件路径 
     */
    private function __get_source_file_path() {
        if (!$this->__source_file_path) {
            if (defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$this->app->app_id.'/setting.php')) {
                $this->__source_file_path = CUSTOM_CORE_DIR.'/'.$this->app->app_id.'/setting.php';
            }else{
                $this->__source_file_path = $this->app->app_dir.'/setting.php';
            }
        }
        return $this->__source_file_path;
    }

    /*
     * 构造函数
     * @access public
     * @param object APP对象 
     * @return void
     */
    public function __construct($app){
        $this->app = $app;
    }

    /*
     * 加载seeting原文件配置
     * @access public
     * @return array 原文件配置 
     */
    function &source(){
        if (!$this->__app_setting) {
            @include($this->__get_source_file_path());
            $this->__app_setting = (array)$setting;
        }
        return $this->__app_setting;
    }

    /*
     * 通过key值, 获取对应app的value
     * @access public
     * @param string key值
     * @return misc value值
     */
    public function get_conf($key){
        if(!isset($this->__app_conf[$key])){
            $val = syscache::instance('setting')->get('setting/'.$this->app->app_id.'-'.$key);
            $app_setting = $this->source();
            if($val === null){
                if(!is_null($app_setting) && isset($app_setting[$key]['default'])){
                    $val = $app_setting[$key]['default'];
                    //$this->set_conf($key, $val);
                }else{
                    return null;
                }
            }
            
            $this->__app_conf[$key] = $val;
        }
        return $this->__app_conf[$key];
    }

    /*
     * 通过key值, 获取对应app的value
     * @access public     
     * @param string key值
     * @param string value值
     * @return bool 
     */
    public function set_conf($key, $value){
        $filter = array('app'=>$this->app->app_id, 'key'=>$key);
        $data = array('app'=>$this->app->app_id, 'key'=>$key, 'value'=>serialize($value));
        $row = app::get('base')->model('setting')->getRow('1', $filter);
        if ($row) {
            $return = app::get('base')->model('setting')->update($data, array('key' => $key, 'app' => $this->app->app_id));
        } else {
            $return = app::get('base')->model('setting')->insert($data);
        }
        $this->__app_conf[$key] = $value;
        return (bool)$return;
    }
}
