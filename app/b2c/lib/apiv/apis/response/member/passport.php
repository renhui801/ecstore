<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c passport interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_member_passport
{

    var $ttl = 86400;

    private function set_accesstoken($member_id){
        kernel::single("base_session")->start();
        $member_ident = kernel::single("base_session")->sess_id();
        $sess_id = md5($member_ident.'api'.$member_id);
        kernel::single("base_session")->set_sess_id($sess_id);
        return $sess_id;
    }

    /**
     * 根据会员登录名 | 手机号 | 邮箱 返回加密密码参数
     */
    public function get_encrypt_params($params,&$service){
        $params = utils::_filter_input($params);//过滤xss攻击
        $account = app::get('pam')->model('members')->getRow('password_account,createtime',array('login_account'=>$params['uname']));
        if( !$account ){
            return $service->send_user_error('该会员不存在');
        }else{
            $return['account'] = $account['password_account'];
            $return['createtime'] = $account['createtime'];
        }
        return $return;
    }

    /**
     * 会员登录接口
     * @params string $uname 用户名 | 手机号 | 邮箱
     * @params string $password 加密过后的密码
     */
    public function signin($params,&$service){
        $params = utils::_filter_input($params);//过滤xss攻击
        $userPassport = kernel::single('b2c_user_passport');
        $login_type = $userPassport->get_login_account_type($params['uname']);

        $filter = array('login_type'=>$login_type,'login_account'=>$params['uname'],'login_password'=>$params['password']);
        $account = app::get('pam')->model('members')->getList('member_id',$filter);
        if(!$account){
            $error['status'] = 'false';
            $error['message'] = app::get('pam')->_('用户名或密码错误');
            return $error;
        }else{
            $b2c_members_model = app::get('b2c')->model('members');
            $member_id = $account[0]['member_id'];
            $member_data = $b2c_members_model->getList( 'member_lv_id,experience,point', array('member_id'=>$member_id) );
            if( !$member_data ){
                $error['status'] = 'false';
                $error['message'] = app::get('b2c')->_('登录失败：会员数据存在问题,请联系商家或客服');
                return $error;
            }

            $member_data = $member_data[0];
            $member_data['order_num'] = app::get('b2c')->model('orders')->count( array('member_id'=>$member_id) );

            if(app::get('b2c')->getConf('site.level_switch')==1)
            {
                $member_data['member_lv_id'] = $b2c_members_model->member_lv_chk($member_data['member_lv_id'],$member_data['experience']);
            }
            if(app::get('b2c')->getConf('site.level_switch')==0)
            {
                $member_point_model = app::get('b2c')->model('member_point');
                $member_data['member_lv_id'] = $member_point_model->member_lv_chk($member_id,$member_data['member_lv_id'],$member_data['point']);
            }
            $b2c_members_model->update($member_data,array('member_id'=>$member_id));
        }
        $data['status'] = 'true';
        $data['member_id'] = $account[0]['member_id'];
        $data['accesstoken'] = $this->set_accesstoken($member_id);
        $data['message'] = app::get('b2c')->_('登录成功');
        kernel::single('b2c_user_object')->set_member_session($member_id);
        return $data;
    }

    /**
     * 对注册的手机号发生验证码,并且检查注册账号是否合法接口
     * @param mobile int 手机号
     * @return vcode 验证码
     */
    public function send_signup_sms($params,&$service){
        $params = utils::_filter_input($params);//过滤xss攻击
        if( empty($params['mobile']) ){
            $error['status'] = 'false';
            $error['message'] = app::get('b2c')->_('请填写正确的手机号码');
            return $error;
        }
        $userPassport = kernel::single('b2c_user_passport');
        $res = $userPassport->check_signup_account($params['mobile'],$msg);
        if( !$res ){
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }
        if( $msg != 'mobile' ){
            $error['status'] = 'false';
            $error['message'] = app::get('b2c')->_('请填写正确的手机号码');
            return $error;
        }
        $data = $this->send_sms($params['mobile'],'signup',$msg);
        if( !$data ){
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }
        $return['status'] = 'true';
        $return['message'] = '短信发送成功';
        return $return;
    }


    /**
     * 会员注册接口
     */
    public function signup($params,&$service){
        $params = utils::_filter_input($params);//过滤xss攻击
        $userPassport = kernel::single('b2c_user_passport');

        $res = $userPassport->check_signup_account($params['uname'],$msg);
        if( !$res ){
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }

        if( $msg == 'mobile' ){
            $res = kernel::single('b2c_user_vcode')->verify($params['vcode'],$params['uname'],'signup'); 
            if(!$res || empty($params['vcode']) ){
                $msg = app::get('b2c')->_('短信验证错误');
                $error['status'] = 'false';
                $error['message'] = $msg;
                return $error;
            }
        }

        $saveData = $userPassport->pre_signup_process($process_data);
        $process_data['login_name'] = $params['uname'];
        $process_data['login_password'] = $params['password'];
        $saveData['b2c_members']['source'] = 'api';
        $saveData['b2c_members']['regtime'] = $params['createtime'];
        $pamAccount = $userPassport->pre_account_signup_process($process_data);
        $pamAccount['login_password'] = $params['password'];
        $pamAccount['createtime'] = $params['createtime'];
        $saveData['pam_account'] = $pamAccount;

        if( $member_id = $userPassport->save_members($saveData,$msg) ){
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
            $data['is_frontend'] = true;
            $obj_account=app::get('b2c')->model('member_account');
            $obj_account->fireEvent('register',$data,$member_id);
            $return['status'] = 'true';
            $return['message'] = '注册成功';
            return $return;
        }else{
            return $service->send_user_error($msg);
        }
    }

    /**
     * 修改密码接口
     */
    public function change_password($params,&$service){
        $params = utils::_filter_input($params);//过滤xss攻击
        $userPassport = kernel::single('b2c_user_passport');
        $pamMembersModel = app::get('pam')->model('members');

        $pamData = $pamMembersModel->getList('login_password',array('member_id'=>$params['member_id'])); 
        if( empty($params['password_old']) || $params['password_old'] !== $pamData[0]['login_password'] ){
            $msg = app::get('b2c')->_('输入的旧密码与原密码不符！');
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }

        if( empty($params['password_new']) ){
            return $service->send_user_error('参数错误，修改的新密码不能为空');
        }

        $db = kernel::database();
        $db->beginTransaction();
        foreach($pamData as $row){
            if(!$pamMembersModel->update(array('login_password'=>$params['password_new']),array('member_id'=>$params['member_id']))){
                $db->rollBack();
                $error['status'] = 'false';
                $error['message'] = '密码修改失败';
                return $error;
            }
        }
        $db->commit();

        $arr_colunms = kernel::single('b2c_user_object')->get_pam_data('*',$params['member_id']);
        $aData['email'] = $arr_colunms['email'];
        $aData['uname'] = $arr_colunms['local'] ? $arr_colunms['local'] : $arr_colunms['mobile'];
        $aData['uname'] = $aData['uname'] ? $aData['uname'] : $arr_colunms['email'];
        $aData['passwd'] = $data['passwd'];

        //发送邮件或者短信
        $obj_account = app::get('b2c')->model('member_account');
        $obj_account->fireEvent('chgpass',$aData,$member_id);
        $return['status'] = 'true';
        $return['message'] = '密码修改成功';
        return $return;
    }

    /**
     * 找回密码1，根据手机号码发送验证码
     */
    public function lost_send_vcode($params,&$service){
        $userPassport = kernel::single('b2c_user_passport');
        $login_type = $userPassport->get_login_account_type($params['mobile']);
        if($login_type != 'mobile'){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }

        if( !$userPassport->is_exists_login_name($params['mobile'])){
            $msg = app::get('b2c')->_('请填写正确的手机号码');
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }

        $data = $this->send_sms($params['mobile'],'forgot',$msg);
        if( !$data ){
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }
        $return['status'] = 'true';
        $return['message'] = '短信发送成功';
        return $return;
    }

    /**
     * 找回密码2，验证码验证
     */
    public function lost_verify_vcode($params,&$service){
        $params = utils::_filter_input($params);//过滤xss攻击
        $userVcode = kernel::single('b2c_user_vcode');
        $vcodeData = $userVcode->verify($params['vcode'],$params['mobile'],'forgot'); 
        if(!$vcodeData ){
            $msg = app::get('b2c')->_('短信验证错误');
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }

        $pamMembersModel = app::get('pam')->model('members');
        $members = $pamMembersModel->getRow('member_id',array('login_account'=>$params['mobile']));
        $return['status'] = 'true';
        $return['message'] = '短信验证成功';
        $lost_token = $this->set_accesstoken($params['mobile']);
        $_SESSION['token'] = $members['member_id'];
        $return['member_id'] = $members['member_id'];
        $return['lost_token'] = $lost_token;
        return $return;
    }

    /**
     * 找回密码3，设定新密码
     */
    public function lost_reset_password($params,&$service){
        $_GET['sess_id'] = $params['lost_token'];
        kernel::single("base_session")->start();
        if( $_SESSION['token'] != $params['member_id'] ){
            $msg = app::get('b2c')->_('该页面已过期');
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }
        $login_password = $params['password'];
        if( !$login_password ){
            return $service->send_user_error('参数错误,密码不能为空');
        }
        $pamMembersModel = app::get('pam')->model('members');
        $db = kernel::database();
        $db->beginTransaction();
        if(!$pamMembersModel->update(array('login_password'=>$login_password),array('member_id'=>$params['member_id']))){
            $db->rollBack();
            $msg = app::get('b2c')->_('密码修改失败');
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }
        $db->commit();

        $arr_colunms = kernel::single('b2c_user_object')->get_pam_data('*',$params['member_id']);
        $aData['email'] = $arr_colunms['email'];
        $aData['uname'] = $arr_colunms['local'] ? $arr_colunms['local'] : $arr_colunms['mobile'];
        $aData['uname'] = $aData['uname'] ? $aData['uname'] : $arr_colunms['email'];
        $aData['passwd'] = $data['passwd'];

        //发送邮件或者短信
        $obj_account = app::get('b2c')->model('member_account');
        $obj_account->fireEvent('chgpass',$aData,$member_id);
        $return['status'] = 'true';
        $return['message'] = '密码修改成功';
        unset($_SESSION);
        return $return;

    }

    /**
     *发送短信验证码
     */
    private function send_sms($mobile,$type,&$msg){
        $userVcode = kernel::single('b2c_user_vcode');
        $vcode = $userVcode->set_vcode($mobile,$type,$msg);
        if(!$vcode){
            return false;
        }
        //发送验证码 发送短信
        $data['vcode'] = $vcode;
        if( !$userVcode->send_sms($type,(string)$mobile,$data) ){
            $msg = app::get('b2c')->_('短信发送失败');
            return false;
        }
        return $data;
    }
}
