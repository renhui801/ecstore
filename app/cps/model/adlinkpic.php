<?php
/**
 * cps_mdl_adlinkpic
 * 网站联盟推广链接图片明细模型
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_adlinkpic Jun 20, 2011  2:42:52 PM ever $
 */
class cps_mdl_adlinkpic extends dbeav_model {
    
    /**
     * 初始化构造方法
     * @param object $app
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }
    
    /**
     * 根据推广链接id获取具体推广广告图片
     * @access public
     * @param int $adId 推广链接id
     * @param array $aField 获取字段
     */
    public function getImageById($adId, $aField = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $aField);
        //获取推广广告图片
        $arrImg = $this->dump(array('link_id' => $adId), $strCols);
        return $arrImg;
    }
}