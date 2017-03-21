<?php
/**
 * cps_mdl_setting
 * 网站联盟基础信息
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_setting Jun 20, 2011  2:46:35 PM ever $
 */
class cps_mdl_setting extends dbeav_model {
    /**
     * 构造初始化方法
     * @param object $app
     * @access public
     * @version 1 Jun 22, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }
    
    /**
     * 根据关键字获取保存数据
     * @access public
     * @param string $strKey 键值
     * @return string
     * @version 1 Jun 22, 2011 创建
     */
    public function getValueByKey($strKey) {
        //根据关键字获取保存数据
        $strSetting = $this->dump($strKey, 'value');
        return $strSetting['value'];
    }
    
    /**
     * 根据关键字更新保存数据
     * @access public
     * @param string $strKey 键
     * @param string $strVal 值
     * @return bool
     * @version 1 Jun 22, 2011 创建
     */
    public function setValueByKey($strKey, $strVal) {
        //基础信息组装
        $arrSetting = array(
            'skey' => $strKey,
            'value' => $strVal,
        );
        //保存数据
        $rs = $this->save($arrSetting);
        return $rs;
    }
}