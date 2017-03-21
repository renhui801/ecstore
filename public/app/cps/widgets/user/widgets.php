<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='zhaojingna@shopex.cn';
$setting['name']=app::get('cps')->_('联盟会员注册/登录');
$setting['version']='1';
$setting['vary']="user";
$setting['stime']='2011-07-07';
$setting['catalog']=app::get('cps')->_('系统相关');
$setting['usual'] = '0';
$setting['description'] = app::get('cps')->_('本版块无需参数设置，添加本版块到模板页面对应插槽上即可使用。');
$setting['template'] = array(
    'default.html'=>app::get('cps')->_('默认'),
    'bar.html'=>app::get('cps')->_('信息条')
);
?>
