<?php
/**
 * cps_mdl_userweb
 * 网站联盟商网站信息模型
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_userweb Jun 20, 2011  2:52:51 PM ever $
 */
class cps_mdl_userweb extends dbeav_model {
    
    /**
     * 初始化构造
     * @access public
     * @param object $app
     * @version Jun 21, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }
    
    /**
     * 根据用户id获取具体用户网站信息
     * @access public
     * @param int $uid 用户id
     * @param array $aField 要获取的字段
     * @return array
     * @version 2 Jun 22, 2011 修改参数
     */
    public function getUserWebById($uid, $aField = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $aField);
        //具体用户网站信息
        $arrUserWeb = $this->dump(array('u_id' => $uid), $strCols);
        return $arrUserWeb;
    }
    
    /**
     * 获取网站类型
     * @access public
     * @return array
     * @version 1 Jun 21, 2011 创建
     */
    public function getWebType() {
        //网站类型数组，结构：表值=>显示名称
        $arrWebTypes = $this->schema['columns']['webtype']['type'];
        return $arrWebTypes;
    }
}