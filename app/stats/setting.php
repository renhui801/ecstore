<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$setting = array(	
	'site.rsc_rpc' => array('type'=>SET_T_STR,'default'=>'0','desc'=>app::get('stats')->_('生意经接口是否开启')),
	'site.rsc_rpc.url' => array('type'=>SET_T_STR,'default'=>'http://rpc.app.shopex.cn','desc'=>app::get('stats')->_('生意经接口地址')),
	'site.stats.is_open' => array('type'=>SET_T_BOOL,'default'=>false,'desc'=>app::get('stats')->_('生意经是否开通')),
);
