<?php
/**
 * cps_ctl_admin_userorderprofit
 * 联盟商订单佣金控制层类
 * 
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_userorderprofit Jun 20, 2011  4:08:18 PM ever $
 */
class cps_ctl_admin_userorderprofit extends desktop_controller {
    
    public $workground = 'cps_center';

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
     * 列表展示页
     * @access public
     */
    public function index() {
        //列表页面参数
        $params = array(
            'title'=>$this->app->_('推广订单列表'),
            'actions'=>array(),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => false,
            'use_buildin_export' => false,
            'use_buildin_import' => false,
            'use_buildin_filter' => true,
            'use_buildin_setcol' => true,
            'use_buildin_refresh' => true,
            'use_buildin_selectrow' => true,
            'use_buildin_tagedit' => false,
            'use_view_tab' => true,
            'allow_detail_popup' => false,
        );

        $this->finder('cps_mdl_userorderprofit', $params);
    }
}