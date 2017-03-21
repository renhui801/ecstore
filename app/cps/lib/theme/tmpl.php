<?php
/**
 * cps_theme_tmpl
 * CPS模板第三方类
 * 
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_theme_tmpl Jun 30, 2011  11:49:04 AM ever $
 */
class cps_theme_tmpl {

    /**
     * 获取模板列表
     * @access public
     * @param array $ctl 模板列表
     * @return array
     * @version 2 Jun 30, 2011 创建
     */
    public function __get_tmpl_list($ctl) {
        $arrCtl = array(
            'cps_index' => app::get('cps')->_('网站联盟首页'),
        	'cps_common' => app::get('cps')->_('网站联盟页面'),
            'cps_notice' => app::get('cps')->_('网站联盟公告详情'),
        );
        
        return array_merge($ctl, $arrCtl);
    }
}