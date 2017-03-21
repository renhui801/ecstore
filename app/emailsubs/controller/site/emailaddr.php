<?php
/**
 * ShopEx网上商店 邮件订阅类
 * 实现前台邮件订阅与取消
 *
 * @package site
 * @version $Id 2011-8-11 11:03$
 * @author chenping
 * @copyright 2003-2008 Shanghai ShopEx Network Tech. Co., Ltd.
 * @license Commercial
 * =================================================================
 * 版权所有 (C) 2003-2009 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:http://www.shopex.cn/
 * -----------------------------------------------------------------
 * 您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * =================================================================
 */

 class  emailsubs_ctl_site_emailaddr extends site_controller{

     function __construct(&$app){
        parent::__construct($app);
        kernel::single('base_session')->start();
        $this->member_id = $_SESSION['account'][pam_account::get_account_type('b2c')];

        $this->username = $_COOKIE['loginName'];
     }

     /**
      * @description 用户邮件订阅
      * @param void
      * @return void
      */
     public function addNew() {
        $this->begin();
        $aParams = $this->_request->get_post();

        //EMAIL匹配
        if(!preg_match('/(\S)+[@]{1}(\S)+[.]{1}(\w)+/',$aParams['email'])) {
            $this->end(false,$this->app->_('请正确填写email地址!'),'back');
        }

        $emailaddrModel = $this->app->model('emailaddr');
        //判断是否已经申请了邮件订阅
        $is_exist = $emailaddrModel->getList('ea_id',array('ea_email'=>$aParams['email']),0,1);
        if($is_exist) {
            $this->end(false,$this->app->_('邮件订阅失败,您已经参加邮件订阅!'),'back');
        }

        //保存邮件地址
        $data['uname'] = $this->username ? $this->username : '';
        $data['ea_email'] = $aParams['email'];
        $data['member_id'] = $this->member_id ? $this->member_id : 0;
        $result = $emailaddrModel->save($data);
        $msg = $result ? $this->app->_('邮件订阅成功') : $this->app->_('邮件订阅失败');

         $this->end($result,$msg,'back');
     }

     /**
      * @description 用户取消邮件订阅
      * @param void
      * @return void
      */
     public function cancel($email,$sign) {
        if($sign!=md5(STORE_KEY.$email)) {
            $this->splash('failed',null, $this->app->_('您没有权限取消订阅!'));
        }
        //模板设置
        $this->set_tmpl('emailsubs');

        $this->begin();
        $emailaddrModel = $this->app->model('emailaddr');
        $result = $emailaddrModel->delete(array('ea_email'=>$email));
        $this->endonly();
        if(!$result) {
            $this->splash('failed',null, $this->app->_('取消订阅失败!'));
        }
        $b2c_frontpage_ctl = kernel::single('b2c_frontpage');
        $b2c_frontpage_ctl->cookie_path = '/';
        $b2c_frontpage_ctl->set_cookie('cancel_emailsubs_sign',1);
        $_SESSION['cancel_emailsubs_sign'] = true;
        $this->redirect(kernel::base_url(1));
     }
 }