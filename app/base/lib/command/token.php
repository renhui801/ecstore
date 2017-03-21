<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_command_token extends base_shell_prototype{

    var $command_show = '显示直联API “token值”';
    function command_show(){
        $token = base_certificate::token();
        echo $token ."\n";
//        parse_str("direct=true&method=b2c.payment.create&order_bn=20101026134778&money=12&cur_money=12&pay_type=online&payment_tid=1&paymethod=abc&t_gegin=10&t_end=20&ip=127.0.0.1&trade_no=123", $request);
//        echo base_certificate::gen_sign($request) . "\n";
    }
}
