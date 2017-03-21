<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$setting['author']='tylerchao.sh@gmail.com';
$setting['name']='首页主公告资讯';
$setting['version']='1.0.0';
$setting['stime']='2013-07';
$setting['catalog']='文章相关';
$setting['usual'] = '0';
$setting['description']='展示模板使用的公告资讯类挂件';
$setting['userinfo']='';
$setting['tag'] = 'auto';
$setting['template'] = array(
    'default.html'=>app::get('b2c')->_('默认')
);
$setting['limit'] = 5;          //节点下显文章数
$setting['lv'] = 2;             //深度
$setting['styleart'] = 0;       //文章样式统一
$setting['shownode'] = 1;       //是否显示节点名称
$setting['node_id']  = 1;       //默认节点
$selectmaps = kernel::single('content_article_node')->get_selectmaps();
array_unshift($selectmaps, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('---所有---')));
$setting['selectmaps'] = $selectmaps;
$setting['select_order']['order_type'] = array('pubtime'=>'发布时间');
$setting['select_order']['order'] = array('asc'=>'升序','desc'=>'降序');
$setting['showuptime'] = 0; //是否显示文章最后更新时间

?>
