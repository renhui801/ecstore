<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class desktop_ctl_default extends desktop_controller{

    var $workground = 'desktop_ctl_dashboard';
    var $certcheck = false;

    function index(){

        $this->_init_keyboard_setting();

        $desktop_user = kernel::single('desktop_user');

        $menus = $desktop_user->get_work_menu();
        $user_id = $this->user->get_id();
        $set_config = $desktop_user->get_conf('fav_menus',$fav_menus);
        //默认显示5个workground
        $workground_count = (app::get('desktop')->getConf('workground.count'))?(app::get('desktop')->getConf('workground.count')-1):5;
        if(!$set_config){
            $i = 0;
            foreach((array)$menus['workground'] as $key=>$value){
                //if($i++>$workground_count) break;
                $fav_menus[] = $key;

            }
        }


        $obj = kernel::service('desktop_index_seo');
        if(is_object($obj) && method_exists($obj, 'title')){
            $title = $obj->title();
        }else{
            $title = app::get('desktop')->_('管理后台');
        }

        $title = ecos_cactus('desktop','check_demosite',$title);  

        if(is_object($obj) && method_exists($obj, 'title_desc')){
            $title_desc = $obj->title_desc();
        }else{
            $title_desc = 'Powered By ShopEx';
        }

        /*
         检查本地是否有更新，并触发更新
         应用场景：在打补丁包或升级包的时候
         TODO:之后考虑在线安装的情况
        */
        $deploy = kernel::single('base_xml')->xml2array(file_get_contents(ROOT_DIR.'/config/deploy.xml'),'base_deploy');
        $local_has_update = false;
        if(! ($product_version = app::get('base')->getConf('product_version')) ){
            $local_has_update = true;
            app::get('base')->setConf('product_version', $deploy['product_version']);
        } elseif( version_compare($product_version, $deploy['product_version'], '!=')) {
            $local_has_update = true;
            app::get('base')->setConf('product_version', $deploy['product_version']);
        }
        
        if( $local_has_update ) {
            $shell_handle = kernel::single('base_shell_loader');
            kernel::$console_output = false;
            $shell_handle->exec_command('update');
        }

        $this->pagedata['title'] = $title;
        $this->pagedata['title_desc'] = $title_desc;
        $this->pagedata['session_id'] = kernel::single('base_session')->sess_id();
        $this->pagedata['uname'] = $this->user->get_login_name();
        $this->pagedata['param_id'] = $user_id;
        $this->pagedata['menus'] = $menus;
        $this->pagedata['fav_menus'] = (array)$fav_menus;
        $this->pagedata['shop_base']  = kernel::base_url(1);
        $this->pagedata['shopadmin_dir'] = ($_SERVER['REQUEST_URI']);
        $desktop_user->get_conf('shortcuts_menus',$shortcuts_menus);
        $this->pagedata['shortcuts_menus'] = (array)$shortcuts_menus;
        $desktop_menu = array();
        foreach(kernel::servicelist('desktop_menu') as $service){
            $array = $service->function_menu();
            $desktop_menu = (is_array($array)) ? array_merge($desktop_menu, $array) : array_merge($desktop_menu, array($array));
        }
        // 桌面内容替换埋点
        foreach( kernel::servicelist('desktop_content') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'changeContent') ) {
                    $services->changeContent(app::get('desktop'));
                    $services->changeContent($desktop_menu);
                }
            }
        }
        $this->pagedata['desktop_menu'] = (count($desktop_menu)) ? '<span>'.join('</span>|<span>', $desktop_menu).'</span>' : '';
        list($this->pagedata['theme_scripts'],$this->pagedata['theme_css']) =
            desktop_application_theme::get_files($this->user->get_theme());

        $this->Certi = base_certificate::get('certificate_id');
        $confirmkey = $this->setEncode($this->pagedata['session_id'],$this->Certi);
        $this->pagedata['certificate_url'] = "http://key-service.shopex.cn/index.php?sess_id=".urlencode($this->pagedata['session_id'])."&certi_id=".urlencode($this->Certi)."&version=ecstore&confirmkey=".urlencode($confirmkey)."&_key_=do";

        $commerce_class = kernel::single('system_commerce');
        if($commerce_class->get_commerce_version()){
            $this->pagedata['commerce_b2c'] = true;
        }
        $this->display('index.html');

    }

    function setEncode($sess_id,$certi_id){
        $ENCODEKEY='ShopEx@License';
        $confirmkey = md5($sess_id.$ENCODEKEY.$certi_id);
        return $confirmkey;
    }

    public function set_open_api()
    {
        echo base_certificate::get_certi_logo_url();exit;
    }


    function set_main_menu(){
        $desktop_user = new desktop_user();
        $workground = $_POST['workgrounds'];
        $desktop_user->set_conf('fav_menus',$workground);
        header('Content-Type:text/jcmd; charset=utf-8');

        echo '{success:"'.app::get('desktop')->_("保存成功！").'"
        }';
    }





    function allmenu(){
        $desktop_user = new desktop_user();
        $menus = $desktop_user->get_work_menu();
        $desktop_user->get_conf('shortcuts_menus',$shortcuts_menus);

        foreach($menus['workground'] as $k=>$v){
            $v['menu_group'] = $menus['menu'][$k];
            $workground_menus[$k]  = $v;
        }
        $this->pagedata['menus'] = $workground_menus;
        $this->pagedata['shortcuts_menus'] = (array)$shortcuts_menus;
        $this->display('allmenu.html');

    }

    function main_menu_define(){
        $desktop_user = kernel::single('desktop_user');

        $menus = $desktop_user->get_work_menu();
        $user_id = $this->user->get_id();
        $set_config = $desktop_user->get_conf('fav_menus',$fav_menus);
        //默认显示5个workground
        $workground_count = (app::get('desktop')->getConf('workground.count'))?(app::get('desktop')->getConf('workground.count')-1):5;
        if(!$set_config){
            $i = 0;
            foreach((array)$menus['workground'] as $key=>$value){
                //if($i++>$workground_count) break;
                $fav_menus[] = $key;
            }
        }

        $this->pagedata['fav_menus'] = (array)$fav_menus;
        $this->pagedata['menus'] = $menus;
        $this->display('main_menu_define.html');
    }


    private function _init_keyboard_setting() {
        $desktop_user = kernel::single('desktop_user');
        $desktop_user->get_conf('keyboard_setting',$keyboard_setting);
        $o = kernel::single('desktop_keyboard_setting');
        $json = $o->get_setting_json( $keyboard_setting );
        $this->pagedata['keyboard_setting_json'] = $json;
    }


    public function keyboard_setting() {
        $desktop_user = kernel::single('desktop_user');
        if( $_POST['keyboard_setting'] ) {
            if ( $this->_keyboard_conflict($_POST['keyboard_setting']) ) {
                $this->begin();
                $this->end(false, '错误：多个快捷键的设置存在冲突');exit;
            } else {
                $desktop_user->set_conf('keyboard_setting',$_POST['keyboard_setting']);
                $this->_init_keyboard_setting();
                echo $this->pagedata['keyboard_setting_json'];exit;
            }
        }

        $desktop_user->get_conf('keyboard_setting',$keyboard_setting);

        //初始化数据
        $o = kernel::single('desktop_keyboard_setting');
        $o->init_keyboard_setting_data( $setting,$keyword,$keyboard_setting );

        foreach( $setting as $key => &$_setting ) {
            foreach( $_setting as &$row ) {
                if( $key!='导航菜单上的栏目' ) {
                    $default = array('ctrl','shift');
                    $o->set_default_control( $default,$row );
                } else {
                    $default = array('alt');
                    $o->set_default_control( $default,$row );
                }
            }
        }

        $this->pagedata['form_action_url'] = $this->app->router()->gen_url( array('app'=>'desktop','act'=>'keyboard_setting','ctl'=>'default') );
        $this->pagedata['keyword'] = $keyword;
        $this->pagedata['setting'] = $setting;
        $this->display('keyboard_setting.html');
    }


    function workground(){
        $wg = $_GET['wg'];
        if(!$wg){
            echo app::get('desktop')->_("参数错误");exit;
        }
        $user = new desktop_user();
        $menus = $this->app->model('menus');
        $group = $user->group();
        $aPermission = array();
        foreach((array)$group as $val){
            #$sdf_permission = $menus->dump($val);
            $aPermission[] = $val;
        }

        if($user->is_super()){
            $sdf = $menus->getList('*',array('menu_type' => 'menu','workground' => $wg));
        }
        else{
            $sdf = $menus->getList('*',array('menu_type' => 'menu','workground' => $wg,'permission' => $aPermission));
        }

        foreach((array)$sdf as $value){
            $url = $value['menu_path'];
            if($value['display'] == 'true'){
                $url_params = unserialize($value['addon']);
                if(count($url_params['url_params'])>0){
                    foreach((array)$url_params['url_params'] as $key => $val){
                        $parmas =$params.'&'.$key.'='.$val;
                    }
                }
                $url = $value['menu_path'].$parmas; break;
            }

        }
        $this->redirect('index.php?'.$url);

    }


    function alertpages(){
        $this->pagedata['goto'] = urldecode($_GET['goto']);
        $this->singlepage('loadpage.html');
    }



    function set_shortcuts(){
        $desktop_user = new desktop_user();
        $_POST['shortcuts'] = ($_POST['shortcuts']?$_POST['shortcuts']:array());
        foreach($_POST['shortcuts'] as $k=>$v){
            list($k,$v) = explode('|',$v);
            $shortcuts[$k] = $v;
        }
        $desktop_user->set_conf('shortcuts_menus',$shortcuts);
        header('Content-Type:text/jcmd; charset=utf-8');
        echo '{success:"'.app::get('desktop')->_("设置成功").'"}';
    }



	/**
	 * 定时触发器, 后端JS定期30秒触发.
	 *
	 * @return null
	 */
    
    function status(){
        //set_time_limit(0);
        ob_start();
        $output = ob_get_contents();
        ob_end_clean();
        echo $output;

        kernel::single('base_session')->close(false);
    }

    function desktop_events(){

        if($_POST['events']){
            foreach($_POST['events'] as $worker=>$task){
                foreach(kernel::servicelist('desktop_task.'.$worker) as $object){
                    $object->run($task,$this);
                }
            }
        }
    }


    function sel_region($path,$depth)
    {
        $path = $_GET['p'][0];
        $depth = $_GET['p'][1];

        header('Content-type: text/html;charset=utf8');
        //$local = app::get('ectools')->model('regions');
        //$ret = $local->get_area_select($path,array('depth'=>$depth));
        $local = kernel::single('ectools_regions_select');
        $ret = $local->get_area_select(app::get('ectools'),$path,array('depth'=>$depth));
        if($ret){
            echo '&nbsp;-&nbsp;'.$ret;
        }else{
            echo '';
        }
    }

    public function about_blank(){
        echo '<html><head></head><body>ABOUT_BLANK_PAGE</body></html>';
    }

    /**
     * keyboard shortcut key conflict check
     * @param array $arr
     * @author Zhang Junhua
     * @return boolean: true if conflict; false if no conflict
     */
    private function _keyboard_conflict( $arr ) {
        //$desktop_user || ($desktop_user = kernel::single('desktop_user'));
        if ( !isset($arr) || empty($arr) ) return false;

        $unique = array();
        foreach( $arr as $col1 ) {
            foreach( $col1 as $col2 ) {
                if ( 'true' == $col2['use'] ) {
                    $tmp = '';
                    foreach ( $col2['params']['control'] as $control=>$true ) {
                        if ( 'true' == $true ) $tmp .= $control;
                    }
                    $tmp .= $col2['params']['keyword'];
                    $unique[] = $tmp;
                }
            }
        }
        return count($unique) !== count(array_unique($unique));
    }

    public function syntax_highlighter(){
        $this->pagedata['id'] = $_GET['id'];
        $this->pagedata['mode'] = $_GET['mode'];
        $this->pagedata['desktop_res_url'] = $this->app->res_url;
        $this->display('codemirror.html');
    }
}
