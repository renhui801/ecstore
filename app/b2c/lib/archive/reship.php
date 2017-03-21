<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_archive_reship extends archive_finder_abstract implements archive_finder_interface{

    public function finder(){
        return array(
            'model' => 'b2c_mdl_archive_reship',
            'params' => array(
                'title'=>app::get('b2c')->_('归档退货单'),
                'use_buildin_recycle'=>false,
                'allow_detail_popup'=>true,
            ),
        );
    }

}
