<?php
/**
 * cps_mdl_adlink
 * 网站联盟推广链接模型
 * 
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_adlink Jun 20, 2011  2:38:14 PM ever $
 */
class cps_mdl_adlink extends dbeav_model {
    
    public $defaultOrder = 'link_id DESC';
    
    /**
     * 根据推广广告id获取具体的广告详情
     * @access public
     * @param int $adId 广告id
     * @param array $aField 显示字段
     * @return array
     * @version 1 Jun 23, 2011 创建
     */
    public function getAdLinkById($adId, $aField = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $aField);
        //获取推广广告链接
        $arrAdLink = $this->dump($adId, $strCols);
        return $arrAdLink;
    }
    
    /**
     * 快速获取代码的相关图片信息list
     * @access public
     * @return array
     * @version Jun 22, 2011 创建
     */
    public function getAdLinkImageList() {
        //查询字段
        $strCols = '*';
        //查询sql
        $strSql = 'SELECT ' . $strCols . ' FROM sdb_cps_adlink AS a LEFT JOIN sdb_cps_adlinkpic AS b ON a.link_id = b.link_id';
        //获取查询结果
        $arrRs = $this->db->select($strSql);
        return $arrRs;
    }
    
    /**
     * 获取推广链接的样式(文字或图片)
     * @access public
     * @return array
     * @version Jun 22, 2011 创建
     */
    public function getAdLinkType() {
        //获取推广链接的样式
        $arrAdLinkTypes = $this->dbschema['columns']['a_type']['type'];
        return $arrAdLinkTypes;
    }
}