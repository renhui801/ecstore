<?php
/**
 * 网站联盟流程widget系统参数
 * $version 1 Jul 4, 2011 创建
 */
//版本
$setting['version'] = '1';
//显示名称
$setting['name'] = app::get('cps')->_('网站联盟流程');
//创建时间
$setting['stime'] = '2011-07-01';
//归属目录
$setting['catalog'] = app::get('cps')->_('辅助工具');

$setting['usual'] = '0';
//描述
$setting['description'] = app::get('cps')->_('网站联盟流程');
//模板
$setting['template'] = array(
    'default.html' => app::get('cps')->_('默认')
);
?>