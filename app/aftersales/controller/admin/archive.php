<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class aftersales_ctl_admin_archive extends desktop_controller{

    var $workground = 'ectools.wrokground.order';


    function returnproduct(){
        kernel::single('aftersales_archive_returnProduct')->set_params($_POST)->display();

    }


}
