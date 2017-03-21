<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_archive_refund extends archive_finder_abstract implements archive_finder_interface{

    public function finder(){
        return array(
            'model' => 'ectools_mdl_archive_refunds',
            'params' => array(
                'title'=>app::get('b2c')->_('归档退款单'),
                'allow_detail_popup'=>true,
                'use_buildin_recycle'=>false,
            ),
        );
    }

}
