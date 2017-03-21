<?php
/**
 * cps_mdl_userpayaccount
 * 网站联盟商收款账户模型
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_userpayaccount Jun 20, 2011  2:51:04 PM ever $
 */
class cps_mdl_userpayaccount extends dbeav_model {

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
     * 根据用户id获取具体用户收款账户信息
     * @access public
     * @param int $userId 用户id
     * @param array $aField 要获取的字段
     * @return array
     * @version Jun 22, 2011 修改参数
     */
    public function getUserPayAccountById($uid, $aField = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $aField);
        //根据用户id获取收款账户信息
        $userPayAccount = $this->dump($uid, $strCols);
        return $userPayAccount;
    }
}