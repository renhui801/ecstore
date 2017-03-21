<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting['author']='ShopEx';
$setting['name']=app::get('cps')->_('图片轮播(FLASH)');
$setting['version']='v2';
$setting['order']=20;

$setting['stime']='2010-8-12';
//,product,goods:act,
//$setting['scope']=array('');
$setting['catalog']=app::get('cps')->_('广告相关');

$setting['description']    = app::get('cps')->_('精巧的Flash展示自己定义的图片广告');

$setting['usual']    = '0';

$setting['template'] = array(
                            'default.html'=>app::get('cps')->_('默认')
                        );

?>
