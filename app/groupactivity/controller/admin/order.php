<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class groupactivity_ctl_admin_order extends desktop_controller
{
    
    function __construct( &$app )
    {
        $this->app = $app;
        parent::__construct( $app );
    }
    
    /*
     * finder列表页
     */
    public function index()
    {
        $this->finder('groupactivity_mdl_orders',array(
            'title'=>app::get('groupactivity')->_('团购订单列表'),
            'allow_detail_popup'=>true,
            'use_buildin_set_tag'=>true,'use_buildin_recycle'=>false,'use_view_tab'=>true,
            'object_method' => array('count'=>'count_finder','getlist'=>'get_list_finder'),
            ));
    }
    #End Func
}