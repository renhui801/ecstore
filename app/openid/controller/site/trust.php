<?php
class openid_ctl_site_trust extends b2c_frontpage{

    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
    }


    //信任登录回调函数(token_url)
    function callback(){
            app::get('openid')->setConf('trust_token',$_GET['token']);
            $callback = kernel::single('pam_callback');
            $params['module'] = 'openid_passport_trust';
            $params['type'] = pam_account::get_account_type('b2c');
            $back_url = $this->gen_url(array('app'=>'openid','ctl'=>'site_trust','act'=>'post_login','full'=>1));
            $params['redirect'] = base64_encode($back_url);
            $callback->login($params);
            if($result_m['redirect_url']){
                echo "script>window.location=decodeURIComponent('".$result_m['redirect_url']."');</script>";
                exit;
            }else{
                echo "<script>top.window.location='".$back_url."'</script>";
                exit;
            }
    }


    //pam登录后处理(保存信任登录返回的信息)
    function post_login(){
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
        $userPassport = kernel::single('b2c_user_passport');
        $member_id = $userPassport->userObject->get_member_id();
        if($member_id){
            $b2c_members_model = app::get('b2c')->model('members');
            $member_point_model = app::get('b2c')->model('member_point');
            $member_data = $b2c_members_model->getList( 'member_lv_id,experience,point', array('member_id'=>$member_id) );
            if(!$member_data){
                $this->splash('failed',null,app::get('b2c')->_('数据异常，请联系客服'));
            }
            $member_data = $member_data[0];
            $member_data['order_num'] = app::get('b2c')->model('orders')->count( array('member_id'=>$member_id) );
            
            if(app::get('b2c')->getConf('site.level_switch')==1 && $member_data['experience'])
            {
                $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
            }
            if(app::get('b2c')->getConf('site.level_switch')==0 && $member_data['point'])
            {
                $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
            }
            
            $b2c_members_model->update($member_data,array('member_id'=>$member_id));
            #$userPassport->userObject->set_member_session($member_id);
            $this->bind_member($member_id);
            app::get('b2c')->model('cart_objects')->setCartNum();
            $url = $userPassport->get_next_page();
            if(!$url){
                $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            }
            $this->splash('success',$url);
        }else{
            $this->splash('failed',kernel::base_url(1),app::get('b2c')->_('参数错误'));
        }
    }
}
?>
