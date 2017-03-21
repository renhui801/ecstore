<?php
   /**
    * ShopEx licence
    *
    * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
    * @license  http://ecos.shopex.cn/ ShopEx License
    */

class app{

    static private $__instance = array();
    static private $__language = null;
    private $__render = null;
    private $__router = null;
    private $__define = null;
    private $__taskrunner = null;
    private $__checkVaryArr = array();
    private $__langPack = array();
    private $__installed = null;
    private $__actived = null;
    private $__setting = null;

    function __construct($app_id){
        $this->app_id = $app_id;
        $this->app_dir = APP_DIR.'/'.$app_id;
        $this->public_app_dir = PUBLIC_DIR.'/app/'.$app_id;

		$this->res_url = kernel::get_app_statics_host_url().'/'.$app_id.'/statics';
		$this->res_full_url = kernel::get_app_statics_host_url().'/'.$app_id.'/statics';
		$this->lang_url = kernel::get_app_statics_host_url().'/'.$app_id.'/lang';
		$this->lang_full_url = kernel::get_app_statics_host_url().'/'.$app_id.'/lang';
		$this->widgets_url = kernel::get_app_statics_host_url().'/'.$app_id.'/widgets';
		$this->widgets_full_url = kernel::get_app_statics_host_url().'/'.$app_id.'/widgets';

        $this->res_dir = PUBLIC_DIR.'/app/'.$app_id.'/statics';
        $this->widgets_dir = PUBLIC_DIR.'/app/'.$app_id.'/widgets';
        $this->lang_dir = PUBLIC_DIR.'/app/'.$app_id.'/lang';
        //$this->lang_resource = lang::get_res($app_id);  //todo: 得到语言包资源文件结构
        $this->_lang_resource = null;
    }

    static function get($app_id){
        if(!isset(self::$__instance[$app_id])){
            self::$__instance[$app_id] = new app($app_id);
        }
        return self::$__instance[$app_id];
    }

    public function lang_resource($lang=null){
        if (!isset($this->_lang_resource)) {
            $this->_lang_resource = lang::get_res($this->app_id);
        }
        return !isset($lang)?$this->_lang_resource:$this->_lang_resource[$lang];
    }

    public function _($key)
    {
        return lang::_($this->lang_dir, $key, func_get_args());
    }//End Function

    public function lang($res=null, $key=null)
    {
        return lang::get_info($this->app_id, $res, $key);     //取得语言包数据
    }//End Function

    public function render(){
        if(!$this->__render){
            $this->__render = new base_render($this);
        }
        return $this->__render;
    }

    public function controller($controller){
        return kernel::single($this->app_id.'_ctl_'.$controller, $this);
    }

    public function model($model){
        return kernel::single($this->app_id.'_mdl_'.$model, $this);
    }

    public function router(){
        if(!$this->__router){
            if(file_exists($this->app_dir.'/lib/router.php')){
                $class_name = $this->app_id.'_router';
                $this->__router = new $class_name($this);
            }else{
                $this->__router = new base_router($this);
            }
        }
        return $this->__router;
    }

    public function setting() {
        if (!$this->__setting) {
            $this->__setting = new base_setting($this);
        }
        return $this->__setting;
    }

    public function base_url($full=false){
        $c = $full?'full':'part';
        if(!$this->base_url[$c]){
            $part = kernel::$app_url_map[$this->app_id];
            $this->base_url[$c] = kernel::base_url($full).kernel::url_prefix().$part.($part=='/' ? '':'/');
        }
        return $this->base_url[$c];
    }

    public function get_parent_model_class(){
        $parent_model_class = $this->define('parent_model_class');
        return $parent_model_class?$parent_model_class:'base_db_model';
    }

    public function define($path=null){
        if(!$this->__define){
            if(is_dir($this->app_dir) && file_exists($this->app_dir.'/app.xml')){
                $tags = array();
                $file_contents = file_get_contents($this->app_dir.'/app.xml');
                $this->__define = kernel::single('base_xml')->xml2array(
                   $file_contents ,'base_app');
            }else{
                $row = app::get('base')->model('apps')->getList('remote_config',array('app_id'=>$this->app_id));
                $this->__define = $row[0]['remote_config'];
            }
        }
        if($path){
            return eval('return $this->__define['.str_replace('/','][',$path).'];');
        }else{
            return $this->__define;
        }
    }

    public function getConf($key){
        if(cachemgr::enable() && cachemgr::check_current_co_depth()>0){
            $this->check_expires($key, true);
        }//todo：如果存在缓存检查，进行conf检查

        return $this->setting()->get_conf($key);
    }

    public function setConf($key, $value){
        if($this->setting()->set_conf($key, $value)) {
            $this->set_modified($key);
            return true;
        } else {
            return false;
        }
    }

    public function set_modified($key)
    {
        $vary_name = strtoupper(md5($this->app_id . $key));
        $now = time();
        $db = kernel::database();
        $db->exec('REPLACE INTO sdb_base_cache_expires (`type`, `name`, `app`, `expire`) VALUES ("CONF", "'.$vary_name.'", "'.$this->app_id.'", ' .$now. ')', true);
        if($db->affect_row()){
            cachemgr::set_modified('CONF', $vary_name, $now);
            syscache::instance('setting')->set_last_modify();
        }
    }//End Function

    public function check_expires($key, $force=false)
    {
        if($force || (cachemgr::enable() && cachemgr::check_current_co_depth()>0)){
            if(!isset($this->__checkVaryArr[$key])){
                $this->__checkVaryArr[$key] = strtoupper(md5($this->app_id . $key));
            }
            if(!cachemgr::check_current_co_objects_exists('CONF', $this->__checkVaryArr[$key])){
                cachemgr::check_expires('CONF', $this->__checkVaryArr[$key]);
            }
        }
    }//End Function

    function runtask($method,$option=null){
        if($this->__taskrunner===null){
            $this->__taskrunner = false;
            if(defined('CUSTOM_CORE_DIR') && file_exists(CUSTOM_CORE_DIR.'/'.$this->app_id.'/task.php')){
                $taskDir = CUSTOM_CORE_DIR.'/'.$this->app_id.'/task.php';
            }else{
                $taskDir = $this->app_dir.'/task.php';
            }
            if(file_exists($taskDir)){
                require($taskDir);
                $class_name = $this->app_id.'_task';
                if(class_exists($class_name)){
                    $this->__taskrunner = new $class_name($this);
                }
            }
        }
        if(is_object($this->__taskrunner) && method_exists($this->__taskrunner,$method)){
            return $this->__taskrunner->$method($option);
        }else{
            return true;
        }
    }

    function status(){
        if(kernel::is_online()){
            if($this->app_id=='base'){
                if(!kernel::database()->select('SHOW TABLES LIKE "'.kernel::database()->prefix.'base_apps"')){
                    return 'uninstalled';
                }
            }
            $row = @kernel::database()->selectrow('select status from sdb_base_apps where app_id="'.$this->app_id.'"');
            return $row?$row['status']:'uninstalled';
        }else{
            return 'uninstalled';
        }
    }

    function is_installed()
    {
        if(is_null($this->__installed)){
            $this->__installed = ($this->status()!='uninstalled') ? true : false;
        }
        return $this->__installed;
    }//End Function

    function is_actived()
    {
        if(is_null($this->__actived)){
            $this->__actived = ($this->status()=='active') ? true : false;
        }
        return $this->__actived;
    }//End Function

    function remote($node_id){
        return new base_rpc_caller($this,$node_id);
    }

    function matrix($node_id=1,$version=1){
        return new base_rpc_caller($this,$node_id,$version);
    }

    function docs($dir=null){
        $docs = array();
        if(!$dir){
            $dir = $this->app_dir.'/docs';
        }
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if($file{0}!='.' && isset($file{5}) && substr($file,-4,4)=='.t2t' && is_file($dir.'/'.$file)){
                        $rs = fopen($dir.'/'.$file, 'r');
                        $docs[$file] = fgets($rs,1024);
                        fclose($rs);
                    }
                }
                closedir($dh);
            }
        }
        return $docs;
    }

   }
