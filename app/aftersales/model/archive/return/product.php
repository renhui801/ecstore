<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class aftersales_mdl_archive_return_product extends archive_model{

    var $defaultOrder = array('add_time','DESC');

    public function extra_search_info(){
        return array(
            'key'=>array(
                'column'=>'return_id',
                'label'=>'售后单号',
            ),
            'time_column'=>'add_time',
        );
    }

    public function document2time($return_id){
        return array(
            'start' => strtotime(substr($return_id,0,10).'0000'),
            'end' => strtotime(substr($return_id,0,10).'5959'),
        );
    }

    public function modifier_member_id($row)
    {
        if (is_null($row) || empty($row))
        {
            return app::get('ectools')->_('未知会员或非会员');
        }

        $login_name =  kernel::single('b2c_user_object')->get_member_name(null,$row); 
        if($login_name){
            return $login_name; 
        }else{
            return app::get('ectools')->_('未知会员或非会员');
        }
    }

}
