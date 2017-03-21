<?php

class b2c_cache{

    function get_cache_methods() {
        return array(
            array(
                'name' => '商品详情页',
                'app'=>'b2c',
                'ctl'=>'site_product',
                'act'=>'index',
                'expires'=>'300'),
            array(
                'name' => '首页',
                'app'=>'site',
                'ctl'=>'default',
                'act'=>'index',
                'expires'=>'0'),
        );
    }
}