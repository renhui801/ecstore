<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_article(&$setting,&$smarty){
    $setting['order'] or $setting['order'] = 'desc';
    $setting['order_type'] or $setting['order_type'] = 'pubtime';

    $iNodeId = $setting['node_id'];
    $limit = $setting['limit'];

    //过滤条件
    $filter['node_id'] = $iNodeId;
    $filter['platform'] = 'wap';
    $filter['ifpub'] = 'true';
    $filter['pubtime|sthan'] = time();

    $indexsObj = app::get('content')->model('article_indexs');
    $data['articles'] = $indexsObj->getList('*', $filter, 0,$limit, 'pubtime DESC');

    // $nodeObj = app::get('content')->model('article_nodes');
    // $data['currentNodeName'] = $nodeObj->getRow('node_id,node_name', array('node_id'=>$iNodeId), 0,1);
    $data['title'] = $setting['title'];
    $data['nodeId'] = $iNodeId;
    // var_dump($iNodeId);
    return $data;
}

?>
