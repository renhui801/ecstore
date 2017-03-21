<?php
/**
 * cps_service_vcode
 * cps验证码类
 *
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_service_vcode Jul 7, 2011  1:50:49 PM ever $
 */
class cps_service_vcode {

    /**
     * 构造方法
     * @access public
     * @param object $app
     * @version 1 Jul 7, 2011
     */
    public function __construct($app){
        $this->app = $app;
    }

    /**
     * 返回是否使用验证码，设置为使用
     * @access public
     * @return boolean
     * @version 1 Jul 7, 2011
     */
    public function status(){
        return true;
    }
}