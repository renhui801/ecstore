<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_archive_orders extends archive_finder_abstract implements archive_finder_interface{

    public function finder(){
        return array(
            'model' => 'b2c_mdl_archive_orders',
            'params' => array(
                'title'=>app::get('b2c')->_('归档订单'),
                'allow_detail_popup'=>true,
                'actions'=>array(
                    array(
                        'label'=>app::get('b2c')->_('导出'),
                        'submit'=>'index.php?app=importexport&ctl=admin_export&act=export_view&_params[app]=b2c&_params[mdl]=archive_orders',
                        'target' => "dialog::{width:400,height:170,title:'归档订单导出'}",
                    ),
                ),
                'use_buildin_recycle'=>false,
            ),
        );
    }

}
