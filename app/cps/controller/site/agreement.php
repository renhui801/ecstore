<?php
/**
 * cps_ctl_site_agreement
 * 联盟协议控制层类
 * 
 * @uses cps_frontpage
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_site_agreement Jun 20, 2011  5:09:34 PM ever $
 */
class cps_ctl_site_agreement extends cps_frontpage {
    
    /**
     * 初始化构造方法
     * @access public
     * @param object $app
     * @version 1 Jun 29, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }
    
    /**
     * 联盟协议展示页
     * @access public
     * @version 2 Jul 7, 2011
     */
    public function index() {
        $this->set_tmpl('cps_common');
        //获取协议信息
        $this->pagedata['agreement'] = $this->app->model('agreement')->getAgreementInfo();
        $this->page('site/agreement/detail.html');
    }
}