<?php
class weixin_ctl_admin_autoreply extends desktop_controller{

    var $workground = 'wap.workground.theme';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
    }//End Function

    //关注自动回复信息设置
    public function attention(){
        $this->finder(
            'weixin_mdl_message',
            array(
                'title'=>app::get('weixin')->_('关注自动回复'),
                'base_filter'=>array('reply_type'=>'attention'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('绑定消息'),'href'=>'index.php?app=weixin&ctl=admin_autoreply&act=bind_message_view&p[0]=attention','target'=>'dialog::{title:\''.app::get('weixin')->_('绑定消息').'\',width:460,height:260}','icon'=>'sss.ccc'),
                ),
                'use_buildin_recycle'=>true,
            )
        );
    }

    //消息自动回复信息设置
    public function message(){
        $this->finder(
            'weixin_mdl_message',
            array(
                'title'=>app::get('weixin')->_('消息自动回复'),
                'base_filter'=>array('reply_type'=>'message'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('绑定消息'),'href'=>'index.php?app=weixin&ctl=admin_autoreply&act=bind_message_view&p[0]=message','target'=>'dialog::{title:\''.app::get('weixin')->_('绑定消息').'\',width:460,height:260}','icon'=>'sss.ccc'),
                ),
                'use_buildin_recycle'=>true,
            )
        );
    }

    //关键词自动回复信息设置
    public function keywords(){
        $this->finder(
            'weixin_mdl_message',
            array(
                'title'=>app::get('weixin')->_('关键词自动回复'),
                'base_filter'=>array('reply_type'=>'keywords'),
                'actions'=>array(
                    array('label'=>app::get('b2c')->_('绑定消息'),'href'=>'index.php?app=weixin&ctl=admin_autoreply&act=bind_message_view&p[0]=keywords','target'=>'dialog::{title:\''.app::get('weixin')->_('绑定消息').'\',width:460,height:260}','icon'=>'sss.ccc'),
                ),
                'use_buildin_recycle'=>true,
            )
        );
    }

    //绑定消息页面
    public function bind_message_view($reply_type,$id=null){
        // 公众账号
        $publicNumbers = app::get('weixin')->model('bind')->getList('id,name');
        $publicNumbers_options = array();
        foreach($publicNumbers as $row){
            $publicNumbers_options[$row['id']] = $row['name'];
        }
        $this->pagedata['publicNumber'] = $publicNumbers_options;

        if( $id ){
            $data = app::get('weixin')->model('message')->getList('*',array('id'=>intval($id)));
        }
        $this->pagedata['data'] = $data ? $data[0] : array();
        $message_type = $data[0]['message_type'];
        $this->pagedata['data'][$message_type]['message_id'] = $data[0]['message_id'];

        $this->pagedata['reply_type'] = $reply_type;

        $page_view = 'admin/autoreply.html';
        $this->display($page_view);
    }

    //保存绑定消息
    public function save(){
        $this->begin();
        if( isset($_POST['id']) && $_POST['id'] ){
            $data['id'] = intval($_POST['id']);
        }

        $data['bind_id'] = intval($_POST['bind_id']);
        $data['message_id'] = intval($_POST['message_id'][$_POST['message_type']]);
        $data['message_type'] = $_POST['message_type'];
        $data['reply_type'] = $_POST['reply_type'];

        if( empty($data['message_id']) ){
            $this->end(false, app::get('weixin')->_('请选择需要绑定的消息'));
        }

        if( $data['reply_type'] == 'keywords' ){
            if(empty($_POST['keywords'])){
                $this->end(false, app::get('weixin')->_('关键字不能为空'));
            }
            $data['keywords'] = $_POST['keywords'];
            //同一公众账号设置关键字不能重复
            $filter = array(
                'bind_id'=>intval($data['bind_id']),
                'keywords'=>$data['keywords'],
                'reply_type'=>$data['reply_type']
            );
            $tmpRow = app::get('weixin')->model('message')->getList('id,reply_type', $filter);
            if( (!$data['id'] && $tmpRow) || ( $tmpRow && $data['id'] && $tmpRow[0]['id'] != $data['id']) ){
                $this->end(false, app::get('weixin')->_('该关键字的公众账号已绑定过自动回复消息'));
            }
        }else{
            if($data['reply_type'] == 'attention'){
                $errorMsg = app::get('weixin')->_('该公众账号已绑定过关注自动回复消息，请进行编辑'); 
            }else{
                $errorMsg = app::get('weixin')->_('该公众账号已绑定过消息自动回复消息，请进行编辑'); 
            }
            $msgData = app::get('weixin')->model('message')->getList('id',array('bind_id'=>intval($data['bind_id']),'reply_type'=>$data['reply_type']) );
            if( (!$data['id'] && $msgData) || ( $msgData && $data['id'] && $msgData[0]['id'] != $data['id']) ){
                $this->end(false, $errorMsg);
            }
        }

        if( app::get('weixin')->model('message')->save($data) ){
            $this->end(true, app::get('weixin')->_('添加成功！'));
        }else{
            $this->end(false, app::get('weixin')->_('添加失败！'));
        }
    }
}

