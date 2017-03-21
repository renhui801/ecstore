<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class b2c_ctl_wap_passport extends wap_frontpage{
    function __construct(&$app){
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        kernel::single('base_session')->start();
        $this->userObject = kernel::single('b2c_user_object');
        $this->userPassport = kernel::single('b2c_user_passport');
    }

    /*
     * 如果是登录状态则直接跳转到会员中心
     * */
    public function check_login($mini){
        if( $this->userObject->is_login() )
        {
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            if($_GET['mini_passport']==1 || $mini){
                kernel::single('wap_router')->http_status(302);return;
            }else{
                //您已经是登录状态，不需要重新登录
                $this->redirect($url);
            }
        }
        return false;
    }

    public function index(){
        //如果会员登录则直接跳转到会员中心
        $this->check_login();
        $this->login();
    }

    /*
     * 登录view
     * */
    public function login($mini=0){
        //如果会员登录则直接跳转到会员中心
        $this->check_login($mini);

        $flag = false;
        if($_GET['mini_passport']==1 || $mini) {
            $flag = true;
            $this->pagedata['mini_passport'] = 1;
        }

        //是否开启验证码
        $this->pagedata['show_varycode'] = kernel::single('b2c_service_vcode')->status();

        #//信任登录openapi
        #foreach(kernel::servicelist('openid_imageurl') as $object){
        #    if(is_object($object)){
        #        if(method_exists($object,'get_image_url')){
        #            $login_image_url[] = $object->get_image_url();
        #        }
        #    }
        #}
        #$this->pagedata['login_image_url'] = $login_image_url;
        $this->pagedata['loginName'] = $_COOKIE['loginName'];

        $this->userPassport->set_next_page('mobile');
        $this->set_tmpl('passport');
        $this->page('wap/passport/index.html');
    }//end function

    /**
     * 检查登陆账号是否需要开启手机验证
     */
    public function login_ajax_account(){
        $login_account = trim($_POST['uname']);
        if( $this->userPassport->check_login_account($login_account,$msg) ){
            echo json_encode(array('needVerify'=>'true'));exit;
        }else{
            echo json_encode(array('needVerify'=>'false'));exit;
        }
    }

    //微信绑定
    public function loginbind(){
        if( $_SESSION['weixin_u_openid'] ){
            $bind_tag = app::get('pam')->model('bind_tag')->getRow('member_id',array('open_id'=>$_SESSION['weixin_u_openid']));
            if( $bind_tag ){
                $msg = app::get('b2c')->_('您已绑定，不需要重新绑定');
                $this->splash('failed',null,$msg);exit;
            }
        }
        $this->unset_member();
        $_SESSION['is_bind_weixin'] = true;
        $this->login();
    }

    public function bindstatus(){
        if( $_SESSION['weixin_u_openid'] ){
            $bind_tag = app::get('pam')->model('bind_tag')->getRow('tag_type,tag_name,id',array('open_id'=>$_SESSION['weixin_u_openid']));
            if( !$bind_tag ){
                $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'loginbind'));
                $this->redirect($url);
            }else{
                $this->pagedata['name'] = $bind_tag['tag_name'] ? $bind_tag['tag_name'] : $bind_tag['tag_type'].'_'.$bind_tag['id'];
                $this->page('wap/bind/bindstatus.html');
            }
        }else{
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'login'));
            $this->redirect($url);
        }
    }

    public function unbindweixin($newbind=false){
        $bind_tag = app::get('pam')->model('bind_tag')->delete(array('open_id'=>$_SESSION['weixin_u_openid']));
        if( $newbind ){
            $this->unset_member();
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'loginbind'));
            $this->redirect($url);
        }else{
            $msg = app::get('b2c')->_('解除绑定成功');
            $this->splash('success',null,$msg);exit;
        }
    }

    /*
     * 登录验证
     * */
    public function post_login(){
        //_POST过滤
        $userData = array(
            'login_account' => $_POST['uname'],
            'login_password' => $_POST['password']
        );

        //是否需要进行手机验证
        if( !kernel::single('b2c_user_vcode')->mobile_login_verify($_POST['mobileVcode'],$_POST['uname'],'activation')){
            $msg = app::get('b2c')->_('手机短信验证码错误'); 
            $this->splash('failed',null,$msg,'','',true);exit;
        }

        if(kernel::single('b2c_service_vcode')->status() && empty($_POST['verifycode'])){
            $msg = app::get('b2c')->_('请输入验证码!');
            $this->splash('failed',null,$msg,'','',true);exit;
        }

        $member_id = kernel::single('pam_passport_site_basic')->login($userData,$_POST['verifycode'],$msg);
        if(!$member_id){
            //设置登陆失败错误次数 一个小时三次错误后需要自动开启验证码
            kernel::single('b2c_service_vcode')->set_error_count();
            $this->splash('failed',null,$msg,'','',true);exit;
        }

        $b2c_members_model = $this->app->model('members');
        $member_point_model = $this->app->model('member_point');

        $member_data = $b2c_members_model->getList( 'member_lv_id,experience,point', array('member_id'=>$member_id) );
        if(!$member_data){
            kernel::single('b2c_service_vcode')->set_error_count();
            //在登录认证表中存在记录，但是在会员信息表中不存在记录
            $msg = $this->app->_('登录失败：会员数据存在问题,请联系商家或客服');
            $this->splash('failed',null,$msg,'','',true);exit;
        }

        $member_data = $member_data[0];
        $member_data['order_num'] = $this->app->model('orders')->count( array('member_id'=>$member_id) );

        if($this->app->getConf('site.level_switch')==1)
        {
            $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
        }
        if($this->app->getConf('site.level_switch')==0)
        {
            $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
        }

        $b2c_members_model->update($member_data,array('member_id'=>$member_id));
        $this->userObject->set_member_session($member_id);
        $this->bind_member($member_id);
        $this->set_cookie('loginName',$_POST['uname'],time()+31536000);//用于记住用户名
        $this->app->model('cart_objects')->setCartNum();
        $bindOpenId = app::get('pam')->model('bind_tag')->getRow('member_id',array('open_id'=>$_SESSION['weixin_u_openid']));
        $bindMember = app::get('pam')->model('bind_tag')->getRow('member_id',array('member_id'=>$member_id));
        if( $_SESSION['weixin_u_openid'] && !$bindOpenId && !$bindMember ){
            $bindWeixinData = array(
                'member_id' => $member_id,
                'open_id' => $_SESSION['weixin_u_openid'],
                'tag_name' => $_SESSION['weixin_u_nickname'],
                'createtime' => time()
            );
            $flag = app::get('pam')->model('bind_tag')->save($bindWeixinData);
            $url = kernel::single('wap_frontpage')->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'weixin_bind_view','arg0'=>$flag));
            $this->splash('success',$url,'','','',true);
        }else{
            $url = $this->userPassport->get_next_page('mobile');
            if( !$url ){
                $url = kernel::single('wap_frontpage')->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            }
            if( $_SESSION['weixin_u_openid'] && $bindMember && $_SESSION['is_bind_weixin'] ){
                $this->unset_member();
                unset($_SESSION['is_bind_weixin']);
                $this->splash('error',null,app::get('b2c')->_('该账号绑定过其他微信账号,不能再进行绑定,请先解除绑定,或再直接登录'),'','',true);
            }else{
                $this->splash('success',$url,app::get('b2c')->_('登录成功'),'','',true);
            }
        }
    }//end function

    /**
     * 绑定成功或失败提示页面
     */
    public function weixin_bind_view($status=true){
        unset($_SESSION['weixin_u_openid']);
        unset($_SESSION['weixin_u_nickname']);
        $this->pagedata['status'] = $status ? 'true' : 'false';
        $this->pagedata['sso_setting'] = app::get('weixin')->getConf('weixin_sso_setting');
        $this->page('wap/bind/bind_tag.html');
    }


    //注册页面
    public function signup($url=null){
        //检查是否登录，如果已登录则直接跳转到会员中心
        $this->check_login();

        $this->userPassport->set_next_page('mobile');

        $this->pagedata['mini_passport'] = $_GET['mini_passport'];

        //获取会员注册项
        $this->pagedata['attr'] = $this->userPassport->get_signup_attr();

        $this->set_tmpl('passport');
        //注册是否需要验证码
        $this->pagedata['valideCode'] = app::get('b2c')->getConf('site.register_valide');

        $this->set_tmpl('passport');

        $this->page("wap/passport/signup.html",$_GET['mini_passport']);
    }

    public function license(){
        $this->pagedata['reg_license'] = app::get('wap')->getConf('wap.register.license');
        $this->page('wap/passport/license.html');
    }

    //注册的时，检查账号
    public function signup_ajax_check_name(){
        $flag = $this->userPassport->check_signup_account( trim($_POST['pam_account']['login_name']),$msg );
        if($flag){
            if($msg == 'mobile'){
                echo json_encode(array('needVerify'=>'true'));exit;
            }
            $this->splash('success',null,$this->app->_('该登录账号可用'),true );exit;
        }else{
            $this->splash('error',null,$msg,true);exit;
        }
    }

   /**
     * create
     * 创建会员
     * 采用事务处理,function save_attr 返回false 立即回滚
     * @access public
     * @return void
     */
    public function create(){
        if($_POST['response_json'] == 'true'){
            $ajax_request = true;
        }else{
            $ajax_request = false;
        }
        
        $_POST['vcode'] = $_POST['pam_account']['login_password'];
        $_POST['pam_account']['psw_confirm'] = $_POST['pam_account']['login_password'];
        if( !$this->userPassport->check_signup($_POST,$msg) ){
            $this->splash('failed',null,$msg,'','',true);exit;
        }

        $saveData = $this->userPassport->pre_signup_process($_POST);

        if($this->from_weixin){
            $saveData['b2c_members']['source'] = 'weixin';
        }else{
            $saveData['b2c_members']['source'] = 'wap';
        }
        if( $member_id = $this->userPassport->save_members($saveData) ){
            $this->userObject->set_member_session($member_id);
            $this->bind_member($member_id);
            foreach(kernel::servicelist('b2c_save_post_om') as $object) {
                $object->set_arr($member_id, 'member');
                $refer_url = $object->get_arr($member_id, 'member');
            }
            /*注册完成后做某些操作! begin*/
            foreach(kernel::servicelist('b2c_register_after') as $object) {
                $object->registerActive($member_id);
            }
            //增加会员同步 2012-5-15
            if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                $member_rpc_object->createActive($member_id);
            }
            /*end*/
            $data['member_id'] = $member_id;
            $data['uname'] = $saveData['pam_account']['login_account'];
            $data['passwd'] = $_POST['pam_account']['psw_confirm'];
            $data['email'] = $_POST['contact']['email'];
            $data['refer_url'] = $refer_url ? $refer_url : '';
            $data['is_frontend'] = true;
            $obj_account=$this->app->model('member_account');
            $obj_account->fireEvent('register',$data,$member_id);

            $url = $this->userPassport->get_next_page('pc');
            if(!$url){
              $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            }
            $this->splash('success',$url,app::get('b2c')->_('注册成功'),'','',true);
        }

        $this->splash('failed',$back_url,app::get('b2c')->_('注册失败'),'','',true);
    }

    /*----------- 次要流程 ---------------*/
    public function lost(){
        $this->check_login();
        $this->path[] = array('title'=>app::get('b2c')->_('忘记密码'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->set_tmpl('passport');
        $this->page("wap/passport/lost.html");
    }

    /*
     * 找回密码页面
     * */
    public function retrieve(){
        $this->check_login();
        $username = $_POST['username'];
        $member_id = $this->userObject->get_member_id_by_username($username);

        if(!$member_id){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'lost'));
            $msg = app::get('b2c')->_('该账号不存在，请检查'); 
            $this->splash('failed',$url,$msg);
        }

        $pamMemberData = app::get('pam')->model('members')->getList('*',array('member_id'=>$member_id));
        foreach($pamMemberData as $row){
            if($row['login_type'] == 'mobile' && $row['disabled'] == 'false'){
                $data['mobile'] = $row['login_account'];
                $verify['mobile'] = true;
            }

            if($row['login_type'] == 'email' && $row['disabled'] == 'false'){
                $data['email'] = $row['login_account'];
                $verify['email'] = true;
            }

            if($row['login_type'] == 'local' && $row['disabled'] == 'false'){
                $data['local'] = $row['login_account']; 
            }
        }

        if(!$verify['email'] && !$verify['mobile']){
            $msg = app::get('b2c')->_('由于您并未验证手机或者邮箱，无法自助找回密码，请联系网站客服！');
            $this->splash('failed',$url,$msg);
        }

        if($verify['mobile'] || $verify['email']){
            $this->pagedata['send_status'] = 'true';
        }
        $this->pagedata['data'] = $data;
        $this->page("wap/passport/lost2.html");
    }


    //发送发送邮件验证码
    public function send_vcode_email()
    {
        $email = $_POST['uname'];
        $type = $_POST['type']; //激活activation

        if( !$type || !$email ){
            $msg = app::get('b2c')->_('请填写正确的邮箱');
            $this->splash('failed',null,$msg,'','',true);
        }

        $login_type = $this->userPassport->get_login_account_type($email);
        if($login_type != 'email'){
            $msg = app::get('b2c')->_('请填写正确的邮箱');
            $this->splash('failed',null,$msg,'','',true);   
        }

        if($type == 'reset' && !$this->userPassport->check_signup_account( trim($email),$msg )){
            $this->splash('failed',null,$msg,'','',true);
        }

        $userVcode = kernel::single('b2c_user_vcode');
        if($email){
            $vcode = $userVcode->set_vcode($email,$type,$msg);
        }
        if($vcode){
            //发送邮箱验证码
            $data['vcode'] = $vcode;
            if( !$userVcode->send_email($type,(string)$email,$data) ){
                $msg = app::get('b2c')->_('参数错误');
                $this->splash('failed',null,$msg,'','',true);
            }
        }else{
            $this->splash('failed',null,$msg,'','',true);
        }    

        $msg = app::get('b2c')->_('发送成功，请查收验证码邮件并填写验证码。');
        $this->splash('success',null,$msg,'','',true);
    }

    //短信发送验证码
    public function send_vcode_sms(){
        $mobile = $_POST['uname'];
        $type = $_POST['type']; //激活activation

        if( !$type || !$mobile ){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $this->splash('failed',null,$msg,'','',true);
        }

        $login_type = $this->userPassport->get_login_account_type($mobile);
        if($login_type != 'mobile'){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $this->splash('failed',null,$msg,'','',true);   
        }

        if($type=='signup' && !$this->userPassport->check_signup_account( trim($mobile),$msg )){
            $this->splash('failed',null,$msg,'','',true);
        }
        
        $userVcode = kernel::single('b2c_user_vcode');
        if($mobile){
            $vcode = $userVcode->set_vcode($mobile,$type,$msg);
        }
        if($vcode){
            //发送验证码 发送短信
            $data['vcode'] = $vcode;
            if( !$userVcode->send_sms($type,(string)$mobile,$data) ){
                $msg = app::get('b2c')->_('发送失败');
                $this->splash('failed',null,$msg,'','',true);
            }
        }else{
            $this->splash('failed',null,$msg,'','',true);
        }
    }

    public function resetpwd_code(){
        $this->check_login();
        $send_type = $_POST['send_type'];
        $userVcode = kernel::single('b2c_user_vcode');
        if( !$vcodeData = $userVcode->verify($_POST[$send_type.'vcode'],$_POST[$send_type],'forgot')){
            $msg = app::get('b2c')->_('验证码错误'); 
            $this->splash('failed',null,$msg,true);exit;
        }
        $data['key'] = $userVcode->get_vcode_key($_POST[$send_type],'forgot');
        $data['key'] = md5($vcodeData['vcode'].$data['key']);
        $data['account'] = $_POST[$send_type]; 
        $this->pagedata['data'] = $data;
        $this->display('wap/passport/lost3.html');
    }

    public function resetpassword(){
        $this->check_login();
        $userVcode = kernel::single('b2c_user_vcode');
        $vcodeData = $userVcode->get_vcode($_POST['account'],'forgot');
        $key = $userVcode->get_vcode_key($_POST['account'],'forgot');
        if($_POST['account'] !=$vcodeData['account']  || $_POST['key'] != md5($vcodeData['vcode'].$key) ){
            $msg = app::get('b2c')->_('页面已过期,请重新找回密码');
            $this->splash('failed',null,$msg,'','',true);exit;
        }

        if( !$this->userPassport->check_passport($_POST['login_password'],$_POST['psw_confirm'],$msg) ){
            $this->splash('failed',null,$msg,'','',true);exit;
        } 

        $member_id = $this->userObject->get_member_id_by_username($_POST['account']);
        if( !$this->userPassport->reset_passport($member_id,$_POST['login_password']) ){
            $msg = app::get('b2c')->_('密码重置失败,请重试');
            $this->splash('failed',null,$msg,'','',true);
        }
        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','login'));
        $msg = app::get('b2c')->_('新密码设置成功，请用新密码登录');
        $this->splash('success',$url,$msg,'','',true);
    }

    public function error(){
        $this->unset_member();
        $back_url = $this->gen_url(array('app'=>'b2c','ctl'=>'wap_passport','act'=>'index'));
        $this->_response->set_redirect($back_url)->send_headers();
    }


    public function logout($url){
        if(!$url){
            $url = array('app'=>'wap','ctl'=>'default','act'=>'index','full'=>1);
        }
        $this->unset_member();
        $this->app->model('cart_objects')->setCartNum($arr);
        $this->redirect($url);
    }

    public function unset_member(){
        $auth = pam_auth::instance(pam_account::get_account_type($this->app->app_id));
        foreach(kernel::servicelist('passport') as $k=>$passport){
           $passport->loginout($auth);
        }
        $this->app->member_id = 0;
        $this->cookie_path = kernel::base_url().'/';
        $this->set_cookie('MEMBER',null,time()-3600);
        $this->set_cookie('UNAME','',time()-3600);
        $this->set_cookie('MLV','',time()-3600);
        $this->set_cookie('CUR','',time()-3600);
        $this->set_cookie('LANG','',time()-3600);
        $this->set_cookie('S[MEMBER]','',time()-3600);
        foreach(kernel::servicelist('member_logout') as $service){
            $service->logout();
        }
    }
}
