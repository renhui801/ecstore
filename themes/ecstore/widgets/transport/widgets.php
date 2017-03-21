<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$setting['author']='tylerchao.sh@gmail.com';
$setting['name']='首页最新发货列表';
$setting['version']='1.0';
$setting['stime']='2013-07';
$setting['catalog']='辅助信息';
$setting['usual'] = '0';
$setting['description'] = '展示模板使用的最新发货清单';
$setting['res_url'] = app::get('b2c')->res_url;
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );
