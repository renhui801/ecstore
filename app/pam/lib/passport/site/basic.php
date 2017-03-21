<?php

/*
 * 前台登录
 * */
class pam_passport_site_basic
{

    /*
     * 前台用户登录验证方法
     *
     * @params $login_account 登录账号
     * @params $login_password 登录密码
     * @params $vcode 验证码
     * */
    public function login($userData,$vcode=false,&$msg){
        $userData = utils::_filter_input($userData);//过滤xss攻击
        if($vcode && !$this->vcode_verify($vcode) ){
            $msg = app::get('pam')->_('验证码错误');
            return false;
        }

        //如果指定了登录类型,则不再进行获取(邮箱登录，手机号登录，用户名登录)
        if(!$userData['login_type']){
            $userPassport = kernel::single('b2c_user_passport');
            $userData['login_type'] = $userPassport->get_login_account_type($userData['login_account']);
        }
        $filter = array('login_type'=>$userData['login_type'],'login_account'=>$userData['login_account']);
        $account = app::get('pam')->model('members')->getList('member_id,password_account,login_password,createtime',$filter);
        if(!$account){
            $msg = app::get('pam')->_('用户名或密码错误');
            return false;
        }
        $login_password = pam_encrypt::get_encrypted_password($userData['login_password'],'member',array('createtime'=>$account[0]['createtime'],'login_name'=>$account[0]['password_account']));

        if($account[0]['login_password'] != $login_password ){
            $msg = app::get('pam')->_('用户名或密码错误');
            return false;
        }
        return $account[0]['member_id'];
    }//end function

    /*
     * 登录验证码验证方法
     *
     * @params $vcode 验证码
     * @return bool
     * */
    public function vcode_verify($vcode){
      if(!base_vcode::verify('b2c',$vcode)){
        return false; 
      }
      return true;
    }
}
