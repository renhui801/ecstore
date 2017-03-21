<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class ectools_task
{

    public function post_install()
    {
        logger::info('Initial ectools');
        kernel::single('base_initial', 'ectools')->init();

        logger::info('Initial Regions');
        kernel::single('ectools_regions_mainland')->install();

        if(!is_dir(DATA_DIR.'/misc')){
            utils::mkdir_p(DATA_DIR.'/misc');
        }
        utils::cp(app::get('ectools')->res_dir.'/js/region_data.js' , DATA_DIR.'/misc/region_data.js');
    }//End Function

    public function post_update( $dbver ){
        if($dbver['dbver'] <= 0.4){
            app::get('ectools')->setConf('ectools_payment_plugin_doubletenpay','a:3:{s:7:"setting";a:7:{s:8:"pay_name";s:18:"财付通双接口";s:7:"pay_fee";s:0:"";s:6:"mer_id";s:0:"";s:10:"PrivateKey";s:0:"";s:11:"support_cur";s:1:"1";s:8:"authtype";s:0:"";s:8:"pay_desc";s:6:"&nbsp;";}s:6:"status";s:5:"false";s:8:"pay_type";s:4:"true";}');
            logger::info('UPDATEING 财付通双接口支付方式更新成功');
        }
    }
}//End Class
