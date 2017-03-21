<?php
class weixin_ctl_admin_message_image extends desktop_controller{

    var $workground = 'wap.workground.weixin';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
        $this->messageImageModel = app::get('weixin')->model('message_image');
    }//End Function

    //图文消息列表
    public function index(){
        $aList = kernel::single('weixin_message')->get_listmaps($id=0, $step=null);
        $this->pagedata['list'] = $aList;
        $this->page("admin/message/image.html");
    }

    //添加图文消息
    public function image_view($parent_id){
        if(!$parent_id){
            $selectmaps = array(0=>array('id'=>0, 'name'=>app::get('weixin')->_('---无---')));
        }else{
            $parentData = $this->messageImageModel->getList('id,name',array('id'=>intval($parent_id) ));
            $selectmaps = array(0=>array('id'=>$parentData[0]['id'], 'name'=>app::get('weixin')->_($parentData[0]['name'])));
        }
        $this->pagedata['selectmaps'] = $selectmaps;
        $this->pagedata['data']['parent_id'] = $parent_id ? $parent_id : '0';

        $this->pagedata['content_type_options'] = $obj_mdl_menus->schema['columns']['content_type']['type'];
        $article_nodes = kernel::single('content_article_node')->get_selectmaps();
        array_unshift($article_nodes, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('--请选择--')));
        $this->pagedata['article_nodes'] = $article_nodes;

        $auth_module = kernel::single('weixin_menus')->auth_module();
        array_unshift($auth_module, array('label' => '--请选择--','url' => '' ) );
        $this->pagedata['auth_module'] = $auth_module;


        $page_view = 'admin/message/image_item.html';
        $this->display($page_view);
    }

    public function edit_image_view($id){
        $data = $this->messageImageModel->getList('id,name,title,picurl,url,parent_id,is_check_bind',array('id'=>intval($id) ));
        if( $data[0]['parent_id'] ){
            $parentData = $this->messageImageModel->getList('id,name',array('id'=>intval($data[0]['parent_id']) ));
            $selectmaps = array(0=>array('id'=>$parentData[0]['id'], 'name'=>app::get('weixin')->_($parentData[0]['name'])));
        }else{
            $selectmaps = array(0=>array('id'=>0, 'name'=>app::get('weixin')->_('---无---')));
        }
        $this->pagedata['selectmaps'] = $selectmaps;
        $this->pagedata['data'] = $data[0] ? $data[0] : array();

        $this->pagedata['content_type_options'] = $obj_mdl_menus->schema['columns']['content_type']['type'];
        $article_nodes = kernel::single('content_article_node')->get_selectmaps();
        array_unshift($article_nodes, array('node_id'=>'', 'step'=>1, 'node_name'=>app::get('content')->_('--请选择--')));
        $this->pagedata['article_nodes'] = $article_nodes;

        $auth_module = kernel::single('weixin_menus')->auth_module();
        array_unshift($auth_module, array('label' => '--请选择--','url' => '' ) );
        $this->pagedata['auth_module'] = $auth_module;

        $page_view = 'admin/message/image_item.html';
        $this->display($page_view);
    }

    //保存图文回复
    public function save_item_imageMessage(){
        $this->begin();

        if( $_POST['parent_id'] ){
            $children_num = $this->messageImageModel->count(array('parent_id'=>intval($_POST['parent_id'])) );
            if( !$_POST['id'] && $children_num >= 8){
                $this->end(false, app::get('weixin')->_('操作失败!您最多可添加 8 条图文'));
            }
        }

        if( empty($_POST['name']) ){
            $this->end(false, app::get('weixin')->_('操作失败!消息名称不能为空'));
        }
        if($row=$this->messageImageModel->getList('id',array('name'=>trim($_POST['name']) )) ){
            if( !$_POST['id'] || $row[0]['id'] != intval($_POST['id']) ){
                $this->end(false, app::get('weixin')->_('操作失败!消息名称已存在'));
            }
        }
        if( empty($_POST['title']) ){
            $this->end(false, app::get('weixin')->_('图文消息标题不能为空！'));
        }
        if( strlen($_POST['title']) > 64){
            $this->end(false, app::get('weixin')->_('图文消息标题不能超过64个字符！'));
        }

        $data = array(
            'name' => $_POST['name'],
            'title' => $_POST['title'],
            'picurl' => trim($_POST['picurl']),
            'url' => trim($_POST['url']),
            'parent_id' => trim($_POST['parent_id']),
            'ordernum' => trim($_POST['ordernum']),
            'is_check_bind' => isset($_POST['is_check_bind']) ? $_POST['is_check_bind'] : 'false',
            'uptime' => time(),
        );

        if( $_POST['parent_id'] ){
            $parentData = $this->messageImageModel->getList('id,has_children',array('id'=>intval($_POST['parent_id'])) );
            if( $parentData[0]['has_children'] == 'false' ){
                $this->messageImageModel->update(array('has_children'=>'true'),array('id'=>intval($_POST['parent_id'])) );
            }
            $data['message_depth'] = 1; 
        }

        if( isset($_POST['id']) && $_POST['id'] ){
            $data['id'] = $_POST['id'];
        }

        if (app::get('weixin')->model('message_image')->save($data) ){
            $refreshUrl = "index.php?app=weixin&ctl=admin_message_image&act=index";
            $this->end(true, app::get('weixin')->_('添加成功！'),$refreshUrl);
        }else{
            $this->end(false, app::get('weixin')->_('添加失败！'));
        }
    }

    public function toRemove($id){
        $this->begin();
        //当我为图文的时候
        $childrenNum = $this->messageImageModel->count(array('parent_id'=>intval($id) ));
        if( $childrenNum ){
            $this->end(false, app::get('weixin')->_('该图文下有子图文，不能删除！'));
        }

        if( app::get('weixin')->model('message')->count(array('message_id'=>$id,'message_type'=>'image')) ){
            $this->end(false, app::get('weixin')->_('该图文消息已被微信消息互动绑定，不能删除'));
        }
        if( app::get('weixin')->model('menus')->count(array('msg_image'=>$id))){
            $this->end(false, app::get('weixin')->_('该图文消息已被自定义菜单绑定，不能删除'));
        }

        //当我为子图文
        $parent_id = $this->messageImageModel->getRow('parent_id',array('id'=>$id));
        //包含我自己和我兄弟的数量
        $countNum = $this->messageImageModel->count(array('parent_id'=>intval($parent_id['parent_id'])));
        if( $countNum == 1 ){
            if( app::get('weixin')->model('message')->count(array('message_id'=>intval($parent_id['parent_id']),'message_type'=>'image')) ){
                $this->end(false, app::get('weixin')->_('该图文消息已被微信消息互动绑定，必须有一条子图文'));
            }
            if( app::get('weixin')->model('menus')->count(array('msg_image'=>intval($parent_id['parent_id']))) ){
                $this->end(false, app::get('weixin')->_('该图文消息已被自定义菜单绑定，必须有一条子图文'));
            }
        }

        if( $this->messageImageModel->delete(array('id'=>$id)) ){
            //如果我delete了之后是否还有兄弟
            if( $countNum == 1 ){
                //没有兄弟了，那么就更新父图文没有儿子了
                $this->messageImageModel->update(array('has_children'=>'false'),array('id'=>$parent_id['parent_id']));
            }
            $refreshUrl = "index.php?app=weixin&ctl=admin_message_image&act=index";
            $this->end(true, app::get('weixin')->_('删除成功！'),$refreshUrl);
        }else{
            $this->end(false, app::get('weixin')->_('删除失败！'));
        }
    }

    public function update(){
        $this->begin('index.php?app=weixin&ctl=admin_message_image&act=index');
        $o = $this->app->model('message_image');
        foreach( (array)$_POST['ordernum'] as $k => $v ){
            $o->update(array('ordernum'=>($v===''?null:$v)),array('id'=>$k) );
        }
        $this->end(true,app::get('b2c')->_('操作成功'));
    }
}
