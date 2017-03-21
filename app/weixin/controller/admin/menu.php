<?php
class weixin_ctl_admin_menu extends desktop_controller{

    function __construct($app)
    {
        parent::__construct($app);
        $this->_request = kernel::single('base_component_request');
        $this->_response = kernel::single('base_component_response');
    }

    public function index()
    {
        $bind_id = $this->_request->get_get('bind_id');
        if(!$bind_id){
            $onebindid = app::get('weixin')->model('bind')->getList('id',array('appid|noequal'=>''),0,1,'id ASC');
            $bind_id = $onebindid[0]['id'];
        }

        // 获取默认菜单
        $this->pagedata['defaultmenu'] = app::get('weixin')->getConf('defaultmenu_'.$bind_id);

        $menu_theme = $this->_request->get_get('menu_theme') ? $this->_request->get_get('menu_theme') : ($this->pagedata['defaultmenu'] ? $this->pagedata['defaultmenu'] : 1);

        $aList = kernel::single('weixin_menus')->get_listmaps($menu_id=0, $step=null, $bind_id, $menu_theme);
        $obj_msg_text = app::get('weixin')->model('message_text');
        $obj_msg_image = app::get('weixin')->model('message_image');
        foreach($aList as &$v){
            if($v['has_children']=='true'){
                $v['content']= '展开二级菜单';
            }else{
                if($v['content_type']=='msg_url'){
                    $v['content'] = '打开链接:  '.( $v['msg_url'] ? $v['msg_url'] : '(尚未配置)' );
                }
                if($v['content_type']=='msg_text'){
                    $tmp_msg_text = $obj_msg_text->getRow('name',array('id'=>$v['msg_text']));
                    $v['content'] = '回复文字:  '.( $tmp_msg_text['name'] ? $tmp_msg_text['name'] : '(尚未配置)' );
                }
                if($v['content_type']=='msg_image'){
                    $tmp_msg_image = $obj_msg_image->getRow('name',array('id'=>$v['msg_image']));
                    $v['content'] = '回复图文:  '.( $tmp_msg_image['name'] ? $tmp_msg_image['name'] : '(尚未配置)' );
                }
            }
        }
        $this->pagedata['list'] = $aList;

        // 公众账号
        $publicNumbers = app::get('weixin')->model('bind')->getList('id,name',array('appid|noequal'=>''));
        $publicNumbers_options = array();
        foreach($publicNumbers as $row){
            $publicNumbers_options[$row['id']] = $row['name'];
        }
        $this->pagedata['publicNumber'] = $publicNumbers_options;

        $this->pagedata['bind_id'] = $bind_id;
        $this->pagedata['menu_theme_id'] = $menu_theme;
        $this->pagedata['menu_theme_arr'] = app::get('weixin')->model('menus')->schema['columns']['menu_theme']['type'];

        $this->page("admin/menus.html");
    }

    public function menus()
    {
        $bind_id = $this->_request->get_get('bind_id');
        // 获取默认菜单
        $this->pagedata['defaultmenu'] = app::get('weixin')->getConf('defaultmenu_'.$bind_id);

        $menu_theme = $this->_request->get_get('menu_theme') ? $this->_request->get_get('menu_theme') : ($this->pagedata['defaultmenu'] ? $this->pagedata['defaultmenu'] : 1);


        $aList = kernel::single('weixin_menus')->get_listmaps($menu_id=0, $step=null, $bind_id, $menu_theme);
        $obj_msg_text = app::get('weixin')->model('message_text');
        $obj_msg_image = app::get('weixin')->model('message_image');
        foreach($aList as &$v){
            if($v['has_children']=='true'){
                $v['content']= '展开二级菜单';
            }else{
                if($v['content_type']=='msg_url'){
                    $v['content'] = '打开链接:  '.( $v['msg_url'] ? $v['msg_url'] : '(尚未配置)' );
                }
                if($v['content_type']=='msg_text'){
                    $tmp_msg_text = $obj_msg_text->getRow('name',array('id'=>$v['msg_text']));
                    $v['content'] = '回复文字:  '.( $tmp_msg_text['name'] ? $tmp_msg_text['name'] : '(尚未配置)' );
                }
                if($v['content_type']=='msg_image'){
                    $tmp_msg_image = $obj_msg_image->getRow('name',array('id'=>$v['msg_image']));
                    $v['content'] = '回复图文:  '.( $tmp_msg_image['name'] ? $tmp_msg_image['name'] : '(尚未配置)' );
                }
            }
        }
        $this->pagedata['list'] = $aList;

        $this->display("admin/menu/list.html");
    }

    public function get_default_theme_menu()
    {
        $bind_id = $this->_request->get_post('bind_id');
        // 获取默认菜单
        $defaultmenu = app::get('weixin')->getConf('defaultmenu_'.$bind_id);

        $menu_theme = $defaultmenu ? $defaultmenu : 1;
        echo json_encode($menu_theme);
    }

    // 添加菜单
    public function add()
    {
        $parent_id = $this->_request->get_get('parent_id');
        $bind_id =  $this->_request->get_get('bind_id');
        $menu_theme =  $this->_request->get_get('menu_theme');
        $obj_mdl_menus = app::get('weixin')->model('menus');
        $obj_lib_menus = kernel::single('weixin_menus');
        if(!$parent_id){
            $parent_menu_nums = $obj_mdl_menus->count(array('bind_id'=>$bind_id,'menu_theme'=>$menu_theme,'menu_depth'=>1));
            if($parent_menu_nums>=3){
                echo app::get('weixin')->_('一级菜单最多只能添加３个');exit;
            }else{
                $parent_id = 0;
            }
        }else{
            $parent_menu_nums = $obj_mdl_menus->count(array('bind_id'=>$bind_id,'menu_theme'=>$menu_theme,'parent_id'=>$parent_id));
            if($parent_menu_nums>=5){
                echo app::get('weixin')->_('二级菜单最多只能添加５个');exit;
            }
        }

        $this->pagedata['menu'] = array('parent_id'=>$parent_id, 'ordernum'=>0,'content_type'=>'msg_url');
        $selectmaps = $obj_lib_menus->get_selectmaps($menu_id=0, $step=1, $bind_id, $menu_theme);
        array_unshift($selectmaps, array('menu_id'=>0, 'step'=>1, 'menu_name'=>app::get('weixin')->_('---无---')));
        $this->pagedata['selectmaps'] = $selectmaps;

        $this->pagedata['menu']['bind_info'] = $bindinfo = app::get('weixin')->model('bind')->getRow('id,name,appid,eid',array('id'=>$bind_id));
        $this->pagedata['menu']['menu_theme_info']['menu_theme_id'] = $menu_theme;
        $this->pagedata['menu']['menu_theme_info']['menu_theme_name'] = $obj_mdl_menus->schema['columns']['menu_theme']['type'][$menu_theme];
        $this->pagedata['content_type_options'] = $obj_mdl_menus->schema['columns']['content_type']['type'];
        $this->pagedata['auth_url_options'] = $obj_lib_menus->get_auth_link($bindinfo['appid'], $bindinfo['eid']);

        $article_nodes = kernel::single('content_article_node')->get_selectmaps();
        array_unshift($article_nodes, array('node_id'=>'', 'step'=>1, 'node_name'=>app::get('content')->_('-- 请选择 --')));
        $this->pagedata['article_nodes'] = $article_nodes;

        $this->page("admin/menu/edit.html");
    }

    // 编辑菜单
    public function edit()
    {
        $menu_id = $this->_request->get_get('menu_id');
        $bind_id = $this->_request->get_get('bind_id');
        $menu_theme = $this->_request->get_get('menu_theme');
        $obj_mdl_menus = app::get('weixin')->model('menus');
        $obj_lib_menus = kernel::single('weixin_menus');
        if(empty($menu_id)){
            $this->splash('error', 'index.php?app=weixin&ctl=admin_menu', app::get('weixin')->_('错误请求'));
        }
        $this->pagedata['menu'] = $obj_mdl_menus->get_by_id($menu_id);
        if(empty($this->pagedata['menu'])){
            $this->splash('error', 'index.php?app=weixin&ctl=admin_menu', app::get('weixin')->_('错误请求'));
        }
        $selectmaps = $obj_lib_menus->get_selectmaps($menu_id=0, $step=1, $bind_id, $menu_theme);
        array_unshift($selectmaps, array('menu_id'=>0, 'step'=>1, 'menu_name'=>app::get('weixin')->_('---无---')));
        $this->pagedata['selectmaps'] = $selectmaps;

        $this->pagedata['menu']['bind_info'] = $bindinfo = app::get('weixin')->model('bind')->getRow('id,name,appid,eid',array('id'=>$bind_id));
        $this->pagedata['menu']['menu_theme_info']['menu_theme_id'] = $menu_theme;
        $this->pagedata['menu']['menu_theme_info']['menu_theme_name'] = $obj_mdl_menus->schema['columns']['menu_theme']['type'][$menu_theme];
        $this->pagedata['content_type_options'] = $obj_mdl_menus->schema['columns']['content_type']['type'];
        $this->pagedata['auth_url_options'] = $obj_lib_menus->get_auth_link($bindinfo['appid'], $bindinfo['eid']);

        $article_nodes = kernel::single('content_article_node')->get_selectmaps();
        array_unshift($article_nodes, array('node_id'=>'', 'step'=>1, 'node_name'=>app::get('content')->_('--请选择--')));
        $this->pagedata['article_nodes'] = $article_nodes;

        $this->page("admin/menu/edit.html");
    }

    // 删除菜单
    public function remove($bind_id, $menu_theme)
    {
        $this->begin();
        $menu_id = $this->_request->get_get('menu_id');
        if(empty($menu_id)){
            $this->end(false, app::get('weixin')->_('错误请求'));
        }
        if(app::get('weixin')->model('menus')->delete(array('menu_id'=>$menu_id))){
            $this->end(true, app::get('weixin')->_('删除成功'));
        }else{
            $this->end(false, app::get('weixin')->_('该菜单存在子菜单，不能被删除'));
        }
    }

    // 保存添加菜单
    public function save()
    {
        $post = $this->_request->get_post('menu');
        $menu_id = $this->_request->get_post('menu_id');
        switch($post['content_type']){
            case 'msg_url':
                unset($post['msg_text']);
                unset($post['msg_image']);
                break;
            case 'msg_text':
                unset($post['msg_url']);
                unset($post['msg_image']);
                break;
            case 'msg_image':
                unset($post['msg_url']);
                unset($post['msg_text']);
                break;
        }
        if(empty($post)){
            $this->splash(error, 'index.php?app=weixin&ctl=admin_menu&act=index', app::get('weixin')->_('错误请求'));
        }
        if(!$post['bind_id']){
            $this->splash(error, 'index.php?app=weixin&ctl=admin_menu&act=index', app::get('weixin')->_('没有选择公众账号或者您还没有添加一个服务号或者具有自定义菜单权限的订阅号!'));
        }
        if(!$post['menu_theme']){
            $this->splash(error, 'index.php?app=weixin&ctl=admin_menu&act=index', app::get('weixin')->_('没有选择一个自定义菜单!'));
        }

        $len_name = ( strlen($post['menu_name']) + mb_strlen($post['menu_name']) ) / 2;
        if($post['parent_id']){
            if($len_name>21){
                $this->splash('error', 'index.php?app=weixin&ctl=admin_menu&act=index', app::get('weixin')->_('二级菜单最多7个汉字或者21个字符!'));exit;
            }
        }else{
            if($len_name>12){
                $this->splash('error', 'index.php?app=weixin&ctl=admin_menu&act=index', app::get('weixin')->_('一级菜单最多4个汉字或者12个字符!'));exit;
            }
        }

        $this->begin();
        $refreshUrl = "index.php?app=weixin&ctl=admin_menu&act=index&bind_id={$post['bind_id']}&menu_theme={$post['menu_theme']}";

        $post['uptime'] = time();

        if($menu_id > 0){
            if( app::get('weixin')->model('menus')->update($post, array('menu_id'=>$menu_id)) ){
                $this->end(true, app::get('weixin')->_('保存成功!'). $msg, $refreshUrl);
            }else{
                $this->end(false, app::get('weixin')->_('保存失败!'). $msg, $refreshUrl);
            }
        }else{
            if(app::get('weixin')->model('menus')->insert($post)){
                $this->end(true, app::get('weixin')->_('添加成功!'). $msg, $refreshUrl);
            }else{
                $this->end(false, app::get('weixin')->_('添加失败!'). $msg, $refreshUrl);
            }
        }
    }

    public function update() {
        $this->begin();
        $bind_id = $this->_request->get_get('bind_id');
        $menu_theme = $this->_request->get_get('menu_theme');
        $refreshUrl = "index.php?app=weixin&ctl=admin_menu&act=index&bind_id={$bind_id}&menu_theme={$menu_theme}";

        $tmp = $_POST['ordernum'];
        is_array($tmp) or $tmp = array();
        $flag = true;
        foreach($tmp as $key => $val) {
            $filter = array('ordernum'=>$val, 'menu_id'=>$key);
            $flag = $this->app->model('menus')->save($filter);
            if(!$flag){
                $this->end(false, app::get('weixin')->_('修改失败!'). $msg, $refreshUrl);
            }
        }
        $this->end(true, app::get('weixin')->_('修改成功!'). $msg, $refreshUrl);
    }

    public function defaultmenu(){
        $bind_id = $this->_request->get_get('bind_id');
        $menu_theme = $this->_request->get_get('menu_theme');
        if($bind_id && $menu_theme){
            // 获取菜单信息数据
            if(!$menu_data = kernel::single('weixin_menus')->get_weixin_menu($menu_id=0, $step=null, $bind_id, $menu_theme)){
                echo json_encode( array( 'errcode'=>'false', 'msg'=>app::get('weixin')->_('菜单不能为空!') ) );exit;
            }
            // 发送微信菜单
            if(!kernel::single('weixin_wechat')->createMenu($bind_id, $menu_data, $msg)){
                echo json_encode( array( 'errcode'=>'false','msg'=>$msg ) );exit;
            }
            // 设置默认菜单
            app::get('weixin')->setConf('defaultmenu_'.$bind_id,$menu_theme);

            echo json_encode( array( 'errcode'=>'true','msg'=>app::get('weixin')->_('设置默认导航菜单成功！') ) );exit;
        }else{
            echo json_encode( array( 'errcode'=>'false','msg'=>app::get('weixin')->_('公众账号和自定义菜单参数有误!') ) );exit;
        }
    }

    public function get_product_url(){
        $product_id = $_POST['product_id'];
        $product_url = app::get('wap')->router()->gen_url( array( 'app'=>'b2c','ctl'=>'wap_product', 'full'=>1, 'arg0'=>$product_id ) );
        echo json_encode($product_url);exit;
    }

    public function get_gallery_url(){
        $cat_id = $_POST['cat_id'];
        $gallery_url = app::get('wap')->router()->gen_url( array( 'app'=>'b2c','ctl'=>'wap_gallery', 'full'=>1, 'arg0'=>$cat_id ) );
        echo json_encode($gallery_url);exit;
    }

    public function get_articlelist_url(){
        $node_id = $_POST['node_id'];
        $articlelist_url = app::get('wap')->router()->gen_url( array( 'app'=>'content','ctl'=>'wap_article', 'act'=>'l', 'full'=>1, 'arg0'=>$node_id ) );
        echo json_encode($articlelist_url);exit;
    }

    public function get_article_url(){
        $article_id = $_POST['article_id'];
        $article_url = app::get('wap')->router()->gen_url( array( 'app'=>'content','ctl'=>'wap_article', 'act'=>'index', 'full'=>1, 'arg0'=>$article_id ) );
        echo json_encode($article_url);exit;
    }

}

