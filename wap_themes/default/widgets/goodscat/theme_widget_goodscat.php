<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

function theme_widget_goodscat(&$setting,&$render){

    $cat_list = getMaps();

    return $cat_list;
}



/**
 * 得到分类的树形结构图
 * @param string depth
 * @param int cat_id
 * @return mixed 结果数据
 */
 function getMaps($depth=-1,$cat_id=0){
    $cat_mdl = app::get('b2c')->model('goods_cat');

    $rows = $cat_mdl->getList('cat_name,cat_id,parent_id,is_leaf,cat_path,type_id',array(),0,-1,'p_order ASC');

    $cats = array();
    $ret = array();
    foreach($rows as $k=>$row){
        if($depth<0 || substr_count($row['cat_path'],',') < $depth){
            $cats[$row['cat_id']] = array(
                'type'=>'gcat',
                'parent_id'=>$row['parent_id'],
                'title'=>$row['cat_name'],
                'link'=>app::get('wap')->router()->gen_url(array('app'=>'b2c', 'ctl'=>'wap_gallery','act'=>'index','args'=>array($row['cat_id']) ))
            );
        }
    }
    foreach($cats as $cid=>$cat){
        if($cat['parent_id'] == $cat_id){
            $ret[] = &$cats[$cid];
        }else{
            $cats[$cat['parent_id']]['items'][] = &$cats[$cid];
        }
    }

    return $ret;
}









