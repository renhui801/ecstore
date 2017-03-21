<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class aftersales_archive_returnProduct extends archive_finder_abstract implements archive_finder_interface{

    public function finder(){
        return array(
            'model' => 'aftersales_mdl_archive_return_product',
            'params' => array(
                'title'=>app::get('b2c')->_('归档售后单'),
                'use_buildin_recycle'=>false,
                'allow_detail_popup'=>true,
            ),
        );
    }

}
