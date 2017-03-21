<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$setting['auther']='tylerchao.sh@gmail.com';
$setting['version']='v1.0';
$setting['name']='主导航菜单';
$setting['stime']='2013-7';
$setting['catalog']='辅助信息';
$setting['usual'] = '0';
$setting['description'] = '展示模板使用的站点主导航挂件';
$setting['userinfo'] ='';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

$cur_url = $_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
$setting['vary'] = $cur_url;

?>
