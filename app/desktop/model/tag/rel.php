<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_mdl_tag_rel extends dbeav_model{

    function save( &$item,$mustUpdate = null,$mustInsert = false){
        $list = parent::getList('*',array('tag_id'=>$item['tag']['tag_id'],'rel_id'=>$item['rel_id']));
        if($list && count($list)>0){
            $item = $list[0];
        }else{
            parent::save($item);
        }
    }
}
