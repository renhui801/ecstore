<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_ctl_admin_member_attr extends desktop_controller{

    var $workground = 'b2c.workground.member';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $attr_model = $this->app->model('member_attr');
        $tmpdate =$attr_model->getList('*',null,0,-1,array('attr_order','asc'));
        #$t_num = count($tmpdate);
        foreach($tmpdate as $key=>$val){
            if($val['attr_type'] == "select" || $val['attr_type'] == "checkbox"){
                $val['attr_option'] = unserialize($val['attr_option']);
            }
            $n_tmpdate[$key] = $val;
        }
        $this->pagedata['tree'] = $n_tmpdate;
        $this->page('admin/member/attr_map.html');
    }

    function add_page(){
        $this->display('admin/member/attr_new.html');
    }
    
    function add(){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index'); 
        $attr_model = $this->app->model('member_attr');
        if($this->check_column($_POST['attr_column'])){
            $this->end(false,app::get('b2c')->_('该注册项字段名已存在'));
        }
        $flag = $attr_model->save($_POST);
        if($flag!=''){
            $this->end(true,app::get('b2c')->_('保存成功！'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败！'));
        }
    }
    
    function check_column($column){
        $member = $this->app->model('members');
        $metaColumn = $member->metaColumn;
        if(in_array($column,(array)$metaColumn)){
            return true;
        }
        else{
            return false;
        }
    }
    
    function edit_page($attr_id){
        $attr_model = $this->app->model('member_attr');
        $data = $attr_model->dump($attr_id);

        if($data['attr_option'] !=''){
            $data['attr_option'] = unserialize($data['attr_option']);
            $data['attr_optionNo1'] = $data['attr_option'][0];
            unset($data['attr_option'][0]);
        }
        $this->pagedata['memattr'] = $data;
        $this->page('admin/member/attr_edit.html');
    }
    
    function edit(){
        if(!$_POST['attr_required']) $_POST['attr_required']="false";
        if($_POST['attr_option'] !=''){
            $_POST['attr_option'] = serialize($_POST['attr_option']);
        }
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index'); 
        $attr_model = $this->app->model('member_attr');    
        if($attr_model->save($_POST)){
            $this->end(true,app::get('b2c')->_('编辑成功！'));
        }else{
           $this->end(false,app::get('b2c')->_('编辑失败！'));
        }
    }
    
    function remove($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = $this->app->model('member_attr');
        $this->end($attr_model->delete($attr_id),app::get('b2c')->_('选项删除成功'));
    }
    
    function show_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = $this->app->model('member_attr');
        $this->end( $attr_model->set_visibility($attr_id,true),app::get('b2c')->_('已设置显示状态'));
    }
    
    function hidden_switch($attr_id){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = $this->app->model('member_attr');
        $this->end( $attr_model->set_visibility($attr_id,false),app::get('b2c')->_('已设置关闭状态'));
    }
    
    function save_order(){
        $this->begin('index.php?app=b2c&ctl=admin_member_attr&act=index');
        $attr_model = $this->app->model('member_attr');
        $this->end( $attr_model->update_order($_POST['attr_order']),app::get('b2c')->_('选项排序更改成功'));
    }
}
