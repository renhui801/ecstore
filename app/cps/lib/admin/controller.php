<?php
/**
 * cps_admin_controller
 * CPS后台基本控制器
 * 
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_admin_controller Jun 20, 2011  4:40:34 PM ever $
 */
class cps_admin_controller extends desktop_controller {
    
    //请求对象属性
    protected $_request = null;
    //响应对象属性
    protected $_response = null;
    
    /**
     * 构造方法
     * @param object $app
     * @access public
     * @version 2 Jun 20, 2011 修改
     */
    public function __construct($app){
        //调用父类构造方法
        parent::__construct($app);
        //初始化请求
        $this->_request = kernel::single('base_component_request');
        //初始化响应
        $this->_response = kernel::single('base_component_response');
    }
}