<?php
/**
 * 微信公众账号绑定
 */
class weixin_ctl_admin_bind extends desktop_controller{

    var $workground = 'wap.workground.weixin';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
        $this->bindModel = app::get('weixin')->model('bind');
    }//End Function

    //绑定列表
    public function index(){
        $this->finder(
            'weixin_mdl_bind',
            array(
            'title'=>app::get('wap')->_('微信公众账号'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加公众账号'),'href'=>'index.php?app=weixin&ctl=admin_bind&act=bind_view','target'=>'dialog::{title:\''.app::get('weixin')->_('添加公众账号').'\',width:600,height:620}','icon'=>'sss.ccc'),
            ),
            'use_buildin_recycle'=>true,
        ));
    }

    /**
     * 微信公众账号配置页面
     */
    public function bind_view($id){
        if( $id ){
            $bindInfo = $this->bindModel->getList('*',array('id'=>$id));
            $this->pagedata['data'] = $bindInfo[0] ? $bindInfo[0] : array();
        }else{
            $bindInfo['id'] = '';
            $bindInfo['eid'] = kernel::single('weixin_object')->get_eid();
            $bindInfo['url'] = kernel::single('weixin_object')->get_weixin_url($bindInfo['eid']);
            $bindInfo['token'] = kernel::single('weixin_object')->set_token();
            $this->pagedata['data'] = $bindInfo;
        }
        $this->pagedata['weixin_type_select'] = array('subscription'=>'订阅号','service'=>'服务号');
        $this->display('admin/bind.html');
    }

    /**
     * 绑定微信公众账号
     */
    public function save_bind(){
        $this->begin();
        if( $_POST['weixin_type'] == 'service' ){
            if( empty($_POST['appid']) ||  empty($_POST['appsecret']) ){
                $this->end(false, app::get('weixin')->_('服务号的appid和appsecret必填!'));
            }
        }

        $data = $this->bindModel->getRow('id',array('name'=>$_POST['name']));
        if( empty($_POST['id']) && $data && $data['id'] != intval($_POST['id']) ){
            $this->end(false, app::get('weixin')->_('公众账号名称已存在'));
        }

        $weixindata = $this->bindModel->getRow('id',array('weixin_account'=>$_POST['weixin_account']));
        if( empty($_POST['id']) && $weixindata && $weixindata['id'] != intval($_POST['id']) ){
            $this->end(false, app::get('weixin')->_('该微信公众账号已配置，请编辑'));
        }

        $this->bindModel->save($_POST);
        $this->end(true, app::get('weixin')->_('添加成功！'));
    }
}
