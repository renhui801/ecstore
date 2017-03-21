<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_mdl_apps extends base_db_model{

    private $_deploy_hidden_app_ids = null;
    private $_deploy_default_app_ids = null;
    private $_deploy_locked_app_ids = null;

    private $_locked_app_ids = null;
    private $_hidden_app_ids = null;

    function filter($filter){
        $addons = array();
        if(isset($filter['installed'])){
            $addons[] = $filter['installed']?'status!="uninstalled"':'status="uninstalled"';
            unset($filter['installed']);
        }
        
        if(isset($filter['normalview'])){ //普通用户浏览模式
            $hidden_apps = true;
            $hidden_app_ids = $this->get_hidden_app_ids();
            if(count($hidden_app_ids)){
                $addons[] = "`app_id` NOT IN ('" . join("', '", $hidden_app_ids) . "')";
            }//todo: 隐藏信赖app信息
                
        }


        $addons = implode(' AND ',$addons);
        if($addons) $addons.=' AND ';
        unset($filter['normalview']);        
        return $addons.parent::filter($filter);
    }

    public function get_deploy_hidden_app_ids(){
        if ($this->_deploy_hidden_app_ids === null) {
            $this->_init_deploy_apps_data();
        }
        return $this->_deploy_hidden_app_ids;
    }

    public function get_deploy_default_app_ids(){
        if ($this->_deploy_default_app_ids === null) {
            $this->_init_deploy_apps_data();
        }
        return $this->_deploy_default_app_ids;
    }

    public function get_deploy_locked_app_ids(){
        if ($this->_deploy_locked_app_ids === null) {
            $this->_init_deploy_apps_data();
        }
        return $this->_deploy_locked_app_ids;
    }
    
    private function _init_deploy_apps_data(){
        $deploy_info = base_setup_config::deploy_info();
        $apps = (is_array($deploy_info['package']['app'])) ? $deploy_info['package']['app'] : array();
        $hidden_app_ids = array();
        $default_app_ids = array();
        $locked_app_ids = array();
        foreach( $apps as $app ){
            if ($app['hidden'] === 'true') {
                array_push($hidden_app_ids, $app['id']);
            }
            if ($app['locked'] === 'true') {
                array_push($locked_app_ids, $app['id']);
            }
            if ($app['default'] === 'true') {
                array_push($default_app_ids, $app['id']);
            }
        }
        $this->_deploy_hidden_app_ids = $hidden_app_ids;
        $this->_deploy_default_app_ids = $default_app_ids;
        $this->_deploy_locked_app_ids = $locked_app_ids;
    }
    
    public function get_locked_app_ids(){
        if ($this->_locked_app_ids === null) {
            $deploy_locked_app_ids = $this->get_deploy_locked_app_ids();
            $depend_app_ids = $this->get_deploy_depend_app_ids();
            $locked_app_ids = array_unique(array_merge($depend_app_ids, $deploy_locked_app_ids));
            $this->_locked_app_ids = $locked_app_ids;
        }
        return $this->_locked_app_ids;
    }


    public function get_hidden_app_ids(){
        if ($this->_hidden_app_ids === null) {
            
            $is_hidden_depend_apps = true;
            //todo: dev开启后目前改为, 隐藏所有hidden apps
            /*
            if($service = kernel::service('base_mdl_apps_hidden')){
                if(method_exists($service, 'is_hidden')){
                    $is_hidden_depend_apps = $service->is_hidden($filter);
                }
            }
            */
                
            if ($is_hidden_depend_apps === true) {
                $deploy_hidden_app_ids = $this->get_deploy_hidden_app_ids();
                $deploy_depend_app_ids = $this->get_deploy_depend_app_ids();
                $hidden_app_ids = array_unique(array_merge($deploy_hidden_app_ids, $deploy_depend_app_ids));
            }else{
                $depend_app_ids = array();
            }
        
            
            $this->_hidden_app_ids = $hidden_app_ids;
        }
        return $this->_hidden_app_ids;
    }
    
	/**
	 * 获取所有默认开启的app所依赖的app_id
	 * 
	 * @return array app_ids
	 */
    public function get_deploy_depend_app_ids() 
    {
        $depend_app_ids = array();
        $default_app_ids = $this->get_deploy_default_app_ids();
        foreach( $default_app_ids as $app_id ){
            $this->check_depend_app_ids($app_id, $depend_app_ids);
        }
        $depend_app_ids = array_unique($depend_app_ids);
        

        $depend_app_ids = array_diff($depend_app_ids, $default_app_ids);
        return $depend_app_ids;
    }//End Functionn

    public function check_depend_app_ids($app_id, &$depend_app_ids){
        $depend_apps = app::get($app_id)->define('depends/app');
        foreach((array)$depend_apps as $depend_app){
            $this->check_depend_app_ids($depend_app['value'],
                                        $depend_app_ids);
        }
        array_push($depend_app_ids, $app_id);
    }
}
