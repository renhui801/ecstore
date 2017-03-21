<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_archive extends desktop_controller{

    var $workground = 'ectools.wrokground.order';

    /**
     * 构造方法
     * @params object app object
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    /**
     * 归档订单列表
     * @return null
     */
    function order(){
        kernel::single('b2c_archive_orders')->set_params($_POST)->display();
    }

    /**
     * 归档发货单
     * @return null
     */
    function delivery(){
        kernel::single('b2c_archive_delivery')->set_params($_POST)->display();
    }

    /**
     * 归档退货单
     * @return null
     */
    function reship(){
        kernel::single('b2c_archive_reship')->set_params($_POST)->display();
    }


}
