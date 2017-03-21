<?php
/**
 *
 * 维权信息查看
 */
class weixin_ctl_admin_business_safeguard extends desktop_controller{

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
            'weixin_mdl_safeguard',
            array(
                'title'=>app::get('weixin')->_('维权信息管理'),
                'use_buildin_recycle'=>true,
                'use_buildin_filter' => true,
            )
        );
    }

    /**
     * 桌面订单相信汇总显示
     * @param null
     * @return null
     */
    public function _views(){
        $safeguardModel = $this->app->model('safeguard');
        $sub_menu = array(
            0=>array('label'=>app::get('aftersales')->_('全部'),'optional'=>false),
            1=>array('label'=>app::get('aftersales')->_('待处理'),'optional'=>false,'filter'=>array('status'=>1)),
            2=>array('label'=>app::get('aftersales')->_('处理中'),'optional'=>false,'filter'=>array('status'=>2)),
            3=>array('label'=>app::get('aftersales')->_('已解决'),'optional'=>false,'filter'=>array('status'=>3)),
        );

        if(isset($_GET['optional_view'])) $sub_menu[$_GET['optional_view']]['optional'] = false;

        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array(),$v['filter']);
                }else{
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                if($k==$_GET['view']){
                    $show_menu[$k]['newcount'] = true;
                    $show_menu[$k]['addon'] = $safeguardModel->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=weixin&ctl=admin_business_safeguard&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

    //接受维权,将接受维权的状态发送到微信端
    public function updatefeedback($id){
        $this->begin();
        $rowData = app::get('weixin')->model('safeguard')->getRow('*',array('id'=>$id));
        if( $rowData['status'] == '1' ){
            $bindData = app::get('weixin')->model('bind')->getRow('*',array('appid'=>$rowData['appid']));
            if( !$bindData ){
                $this->end(false,app::get('weixin')->_('该公众账号以解除绑定关系'));
            }
            $result = kernel::single('weixin_wechat')->updatefeedback($bindData['id'], $rowData['openid'], $rowData['feedbackid']);
            if( !$result ){
                $this->end(false,$result['errmsg']);
            }else{
                $res = app::get('weixin')->model('safeguard')->update(array('status'=>'2'),array('id'=>$id));
                $this->end(true,app::get('weixin')->_('接受维权成功'));
            }
        }else{
            $this->end(true,app::get('weixin')->_('已接受，不需要接受维权'));
        }
    }//end function

}
