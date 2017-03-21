<?php

class b2c_mdl_order_cancel_reason extends dbeav_model{
    public function change_reason_type($reason_type){
        $reason_types = array(
                0 => app::get('b2c')->_('不想要了'),                                                                                        
                1 => app::get('b2c')->_('支付不成功'),
                2 => app::get('b2c')->_('价格较贵'),
                3 => app::get('b2c')->_('缺货'),
                4 => app::get('b2c')->_('等待时间过长'),
                5 => app::get('b2c')->_('拍错了'),
                6 => app::get('b2c')->_('订单信息填写错误'),
                7 => app::get('b2c')->_('其它'),
                );
        return $reason_types[$reason_type];
    } 
}
