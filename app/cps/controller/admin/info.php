<?php
/**
 * cps_ctl_admin_info
 * 后台消息管理控制层类
 *
 * @uses cps_admin_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_info Jun 20, 2011  4:18:28 PM ever $
 */
class cps_ctl_admin_info extends cps_admin_controller {
    
    public $workground = 'cps_center';

    /**
     * 联盟公告与帮助中心列表展示函数(带参flt区分两个菜单)
     * @access public
     * @param string $flt 区分公告与帮助中心
     * @version 3 Jun 24, 2011 显示标题设置
     */
    public function index($flt = '1') {
        //页面显示标题
        $ttl = '联盟公告列表';
        
        //帮助中心显示标题
        if ($flt == '2') {
            $ttl = '帮助中心列表';
        }
        
        //列表页面参数
        $params = array(
            'title'=>$this->app->_($ttl),
            'actions'=>array(
                    array('label'=>$this->app->_('添加文章'),
                    'href'=>'index.php?app=cps&ctl=admin_info_detail&act=add',
                 ),
             ),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => true,
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
        
        //联盟公告与帮忙中心区分
        $params['base_filter'] = array('i_type' => $flt);

        $this->finder('cps_mdl_info', $params);
    }
}