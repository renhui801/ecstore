<?php
/**
 * cps_view_helper
 * CPS显示层类
 * 
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_view_helper Aug 5, 2011  11:42:29 AM ever $
 */
class cps_site_view_helper {
    
    /**
     * 重组前台页面js中的全局变量，追加cookie过期时间
     * @access public
     * @param array $params 参数
     * @param object $smarty 模板实例
     * @return string
     * @version 1 Aug 5, 2011
     */
    public function header_shop_set_extends(&$shop) {
        //获取cookie周期，单位：天
        $cookiePeriod = kernel::single('cps_mdl_setting')->getValueByKey('cookiePeriod');
        //页面参数
        $arr_extends['set']['refer_timeout'] = $cookiePeriod ? $cookiePeriod : 15;

        if(!is_null($shop)){
            $tmp_shop = json_decode($shop,1);
        }else{
            $tmp_shop =array();
        }

        $tmp_arr = array_merge($tmp_shop, $arr_extends);
        $shop = json_encode($tmp_arr);
        return true;
    }
}