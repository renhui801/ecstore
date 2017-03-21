<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class wap_mdl_explorers extends dbeav_model 
{

    public function pre_recycle($params) 
    {
        trigger_error(app::get('wap')->_("此数据不能人为删除"), E_USER_ERROR);
        return false;
    }//End Function
}//End Class
