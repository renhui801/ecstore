<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_mdl_widgets_proinstance extends dbeav_model 
{
    var $has_tag = true;

    public function searchOptions() 
    {
        $arr = parent::searchOptions();
        return array_merge($arr, array(
                'name' => app::get('site')->_('实例名称'),
            ));
    }//End Function

}//End Class
