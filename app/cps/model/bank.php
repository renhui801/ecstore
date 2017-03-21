<?php
/**
 * cps_mdl_bank
 * 网站联盟开户银行
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_bank Jun 20, 2011  2:44:48 PM ever $
 */
class cps_mdl_bank extends dbeav_model {
    
    public $defaultOrder = 'b_id DESC';
    
    /**
     * 构造方法
     * @access public
     * @param object $app
     * @version 1 Jun 22, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }
    
    /**
     * 获取系统默认的银行信息列表
     * @access public
     * @param array $arrFlt 过滤条件
     * @return array $arrCols 查询列
     * @version 2 Jul 5, 2011
     */
    public function getBankList($arrFlt = array(), $arrCols = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $arrCols);
        //获取银行
        $arrBanks = $this->getList($strCols, $arrFlt);
        return $arrBanks;
    }
}