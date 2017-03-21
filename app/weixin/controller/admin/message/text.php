<?php
class weixin_ctl_admin_message_text extends desktop_controller{

    var $workground = 'wap.workground.weixin';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
    }//End Function

    //文字消息列表
    public function index(){
        $this->finder(
            'weixin_mdl_message_text',
            array(
                'title'=>app::get('weixin')->_('文字消息列表'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('添加文字消息'),'href'=>'index.php?app=weixin&ctl=admin_message_text&act=text_view','target'=>'dialog::{title:\''.app::get('weixin')->_('添加文字消息').'\',width:600,height:500}','icon'=>'sss.ccc'),
                ),
                'use_buildin_recycle'=>true,
            )
        );
    }

    //添加文字消息
    public function text_view($id){
        $data = app::get('weixin')->model('message_text')->getList('id,content,name,is_check_bind',array('id'=>intval($id) ));
        $this->pagedata['data'] = $data[0] ? $data[0] : array();
        $page_view = 'admin/message/text.html';
        $this->display($page_view);
    }

    //保存文字回复
    public function save(){
        $this->begin();
        if( empty($_POST['content']) ){
            $this->end(false, app::get('weixin')->_('操作失败!内容不能为空'));
        }
        if( empty($_POST['name']) ){
            $this->end(false, app::get('weixin')->_('操作失败!消息名称不能为空'));
        }
        $_POST['content'] = trim($_POST['content']);
        if( strlen($_POST['content']) > 1200 ){
            $this->end(false, app::get('weixin')->_('操作失败!内容不能超出1200字符'));
        }

        if($row=app::get('weixin')->model('message_text')->getList('id',array('name'=>$_POST['name'])) ){
            if( !$_POST['id'] || $row[0]['id'] != intval($_POST['id']) ){
                $this->end(false, app::get('weixin')->_('操作失败!消息名称已存在'));
            }
        }

        $data = array(
            'content' => trim(str_replace('&nbsp;',' ',$_POST['content'])), 
            'name' => trim($_POST['name']),
            'is_check_bind' => $_POST['is_check_bind'],
        );
        if( isset($_POST['id']) && intval($_POST['id']) ){
            $data['id'] = intval($_POST['id']);
        }
        if (app::get('weixin')->model('message_text')->save($data) ){
            $this->end(true, app::get('weixin')->_('添加成功！'));
        }else{
            $this->end(true, app::get('weixin')->_('添加失败！'));
        }
    }


}
