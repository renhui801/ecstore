<?php
/**
 *
 * 维权信息查看
 */
class weixin_ctl_admin_shop_alert extends desktop_controller{

    var $workground = 'wap.workground.weixin';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
    }//End Function

    //关注自动回复信息设置
    public function index(){
        $this->finder(
            'weixin_mdl_alert',
            array(
                'title'=>app::get('weixin')->_('告警消息查看'),
                'use_buildin_recycle'=>true,
            )
        );
    }



}
