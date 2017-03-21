<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting['author']='tylerchao.sh@gmail.com';
$setting['version']='v1.0';
$setting['usual']    = '0';
$setting['orderby']='1';
$setting['name']='首页最新订单列表';
$setting['catalog']='辅助信息';
$setting['description']    = '展示模板使用的最新订单';
$setting['stime']='2012-10';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
$setting['res_url'] = app::get('b2c')->res_url;

$setting['rowNum'] = 10;
$setting['pageNum'] = 5;
$setting['height'] = 125;
$setting['roll_speed'] = 250;
$setting['steps'] = 1;
?>
