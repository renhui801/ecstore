<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting = array(
    'base.site_params_separator'=>array('type'=>SET_T_ENUM, 'default'=>'-', 'required'=>true, 'options'=>array('-'=>'-'), 'desc'=>app::get('site')->_('URL参数分隔符')),
    'base.site_page_cache'=>array('type'=>SET_T_BOOL, 'default'=>'true', 'required'=>true, 'desc'=>app::get('site')->_('启用全页缓存')),
    'base.enable_site_uri_expanded'=>array('type'=>SET_T_BOOL, 'default'=>'true', 'required'=>true, 'desc'=>app::get('site')->_('启用扩展名'),'javascript'=>' var _site_uri_expanded_name = $$(\'input[name^=set[base.site_uri_expanded_name]\').getParent(\'tr\');
$$(\'input[name^=set[base.enable_site_uri_expanded]\').addEvent(\'click\',function(e){if(this.value==\'false\'){_site_uri_expanded_name.setStyle(\'display\',\'none\');}else {_site_uri_expanded_name.setStyle(\'display\',\'\');}});$$(\'input[name^=set[base.enable_site_uri_expanded]\').each(function(item){if(item.checked && item.value==\'false\')_site_uri_expanded_name.setStyle(\'display\',\'none\');});
    '),
    'base.site_uri_expanded_name'=>array('type'=>SET_T_STR, 'required'=>true, 'default'=>'html', 'desc'=>app::get('site')->_('URL扩展名(例:html)')),
    'base.check_uri_expanded_name'=>array('type'=>SET_T_BOOL, 'required'=>true, 'default'=>'true', 'desc'=>app::get('site')->_('启用URL扩展名检查')),
    'site.name'=>array('type'=>SET_T_STR,'vtype'=>'maxLength', 'default'=>app::get('b2c')->_('点此设置您商店的名称'), 'desc'=>app::get('site')->_('站点名称'),'javascript'=>'validatorMap.set("maxLength",["最大长度32个字",function(el,v){return v.length < 33;}]);'),
    'page.default_title'=>array('type'=>SET_T_STR, 'default'=>'', 'desc'=>app::get('site')->_('网页默认标题')),
    'page.default_keywords'=>array('type'=>SET_T_STR, 'default'=>'', 'desc'=>app::get('site')->_('网页默认关键字')),
    'page.default_description'=>array('type'=>SET_T_TXT, 'default'=>'', 'desc'=>app::get('site')->_('网页默认简介')),
    'system.foot_edit' => array('type'=>SET_T_HTML, 'desc'=>app::get('site')->_('网页底部信息'), 'default'=>'<div class="theme-footer"> 
    <p><b style="color:red;">'.app::get('site')->_('修改本区域内容，请到').' '.app::get('site')->_('商店管理后台').' &gt;&gt; '.app::get('site')->_('站点').' &gt;&gt; '.app::get('site')->_('站点配置').' '.app::get('site')->_('进行编辑').'</b></p>
    <p>© 2013 All rights reserved.</p>
    <p>'.app::get('site')->_('本商店顾客个人信息将不会被泄漏给其他任何机构和个人').'<br/>'.app::get('site')->_('本商店logo和图片都已经申请保护，未经授权不得使用').'<br/>'.app::get('site')->_('有任何购物问题请联系我们在线客服 | 电话：800-800-8000 | 工作时间：周一至周五 8:00－18:00').' </p>
    </div>'),
    'system.site_icp'=>array('type'=>SET_T_STR, 'default'=>'', 'desc'=>app::get('site')->_('备案号')),
);
