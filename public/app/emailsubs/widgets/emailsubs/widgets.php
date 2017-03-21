<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


$setting['author']='chenping';
$setting['version']='1.0';
$setting['name']=app::get('emailsubs')->_('邮件订阅');
//$setting['vary']='emailsubs';
$setting['stime']='2011-8-10 18:13';
$setting['catalog']=app::get('emailsubs')->_('系统相关');
$setting['description']    = app::get('emailsubs')->_('通过邮件订阅功能，将网站活动通过邮件及时通知给用户');
$setting['template'] = array('default.html'=> app::get('emailsubs')->_('默认'));
