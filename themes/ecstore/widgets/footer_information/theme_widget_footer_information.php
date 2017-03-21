<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_footer_information(&$setting,&$smarty){
    $setting['order'] or $setting['order'] = 'desc';
    $setting['order_type'] or $setting['order_type'] = 'pubtime';
    $orderby = $setting['order_type'].' '.$setting['order'];
    $func = array('asc'=>'ksort','desc'=>'krsort');

    $oMAI = app::get('content')->model('article_indexs');

    $filter['ifpub'] = 'true';
    $filter['pubtime|lthan'] = time();
    $filter['article_id'] = $setting['article_id'];
    $arr = $oMAI->getList('*',$filter,0,-1,$orderby);

    $tmp['indexs'] = $arr;
    $tmp['__stripparenturl'] = $setting['stripparenturl'];

    $nodeItem= kernel::single('content_article_node')->get_node($setting['node_id']);
    $tmp['node_name'] = $nodeItem['node_name'];
    if( $tmp['homepage']=='true' )
        $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'i', 'arg0'=>$setting['node_id']) );
    else
        $tmp['node_url'] = app::get('site')->router()->gen_url( array('app'=>'content', 'ctl'=>'site_article', 'act'=>'l', 'arg0'=>$setting['node_id']) );

    return $tmp;
}
?>
