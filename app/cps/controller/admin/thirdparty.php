<?php
/**
 * cps_ctl_thirdparty
 * 第三方CPS后台控制器
 * 
 * @uses cps_admin_controller
 * @package cps
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_thirdparty Jul 29, 2011  3:07:32 PM ever $
 */
class cps_ctl_admin_thirdparty extends cps_admin_controller {
    
    /**
     * 列表页Tab标签
     * @access public
     * @return array
     * @version 1 Aug 2, 2011
     */
    public function _views() {
        $mdlTpo = $this->app->model('thirdparty_orders');
        $tabs = array(
            0 => array('label'=>$this->app->_('全部'), 'filter'=>'', 'optional'=>false),
            1 => array('label'=>$this->app->_('亿起发'), 'filter'=>array('src' => 'emar'), 'optional'=>false),
        );
        $i=0;
        foreach($tabs as $k=>$v){
            $tabs[$k]['filter'] = $v['filter']?$v['filter']:null;
            $tabs[$k]['addon'] = $mdlTpo->count($v['filter']);
            $tabs[$k]['href'] = 'index.php?app=cps&ctl=admin_thirdparty&act=index&view='.$i++;
        }
        return $tabs;
	}
    
    /**
     * 第三方CPS订单列表
     * @access public
     * @param string $src
     * @version 1 Jul 29, 2011
     */
    public function index($src = null) {
        //列表页面参数
        $params = array(
            'title'=>$this->app->_('第三方CPS订单列表'),
            'actions'=>array(),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => false,
            'use_buildin_export' => true,
            'use_buildin_import' => false,
            'use_buildin_filter' => true,
            'use_buildin_setcol' => true,
            'use_buildin_refresh' => true,
            'use_buildin_selectrow' => true,
            'use_buildin_tagedit' => false,
            'use_view_tab' => true,
            'allow_detail_popup' => false,
        );
        $this->finder('cps_mdl_thirdparty_orders', $params);
    }
}