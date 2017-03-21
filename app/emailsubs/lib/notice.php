<?php
/**
 * ShopEx网上商店 邮件订阅提示信息
 *
 * @package lib
 * @version $Id 2011-8-16 11:42$
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


class emailsubs_notice implements site_interface_controller_content{

    /**
     * @description 构造器
     * @access public
     * @param void
     * @return void
     */
    public function __construct(&$app) {
        $this->app = $app;
        kernel::single('base_session')->start();
    }

    /**
     * @description 更改首页
     * @access public
     * @param void
     * @return void
     */
    public function modify(&$html, &$obj) {
        if($_SESSION['cancel_emailsubs_sign']) {
            $cancelNotice = $obj->fetch('site/emailaddr/cancel.html','emailsubs');
            $html = str_ireplace('<body>','<body>'.$cancelNotice,$html);
        }else{
            $b2c_frontpage_ctl = kernel::single('b2c_frontpage');
            $b2c_frontpage_ctl->cookie_path = '/';
            $b2c_frontpage_ctl->set_cookie('cancel_emailsubs_sign',0);
        }
        unset($_SESSION['cancel_emailsubs_sign']);
    }

}

