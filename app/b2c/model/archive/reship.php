<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_archive_reship extends archive_model{

    var $has_many = array(
        'reship_items'=>'archive_reship_items:contrast:reship_id^reship_id',
        'orders'=>'archive_order_delivery:contrast:reship_id^dly_id',
    );

    var $defaultOrder = array('t_begin','DESC');

    public function extra_search_info(){
        return array(
            'key'=>array(
                'column'=>'reship_id',
                'label'=>'退货单号',
            ),
            'time_column'=>'t_begin',
        );
    }

    public function document2time($reship_id){
        return array(
            'start' => strtotime(substr($reship_id,1,8).'000000'),
            'end' => strtotime(substr($reship_id,1,8).'235959'),
        );
    }

    public function modifier_money($row)
    {
        $app_ectools = app::get('ectools');
        $row = $app_ectools->model('currency')->changer_odr($row,null,false,false,$this->app->getConf('system.money.decimals'),$this->app->getConf('system.money.operation.carryset'));
        
        return $row;
    }
    
    public function modifier_delivery($row)
    {
        $obj_dlytype = $this->app->model('dlytype');
        $arr_dlytype = $obj_dlytype->dump($row, 'dt_name');
        
        return $arr_dlytype['dt_name'] ? $arr_dlytype['dt_name'] : '-';
    }

}
