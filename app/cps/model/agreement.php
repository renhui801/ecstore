<?php
/**
 * cps_mdl_agreement
 * 网站联盟联盟协议模型
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_agreement Jun 20, 2011  2:43:51 PM ever $
 */
class cps_mdl_agreement extends dbeav_model {
    
    /**
     * 初始化构造方法
     * @param object $app
     * @version 1 Jun 22, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }
    
    /**
     * 获取联盟协议信息
     * @access public
     * @return array
     * @version 1 Jun 22, 2011 创建
     */
    public function getAgreementInfo() {
        //获取联盟协议
        $arrAgrees = $this->getList();
        //获取一条联盟协议
        $arrAgree = $arrAgrees[0];
        return $arrAgree;
    }
}