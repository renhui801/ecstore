<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class weixin_mdl_message extends dbeav_model
{


    public function _filter($filter,$tableAlias=null,$baseWhere=null){

        if( $filter['search_bind_name'] ){
            $bindData = app::get('weixin')->model('bind')->getRow('id',array('name'=>$filter['search_bind_name']));
            $filter['bind_id'] = $bindData['id'];
            unset($filter['search_bind_name']);
        }
        $filter = parent::_filter($filter);
        return $filter;
    }

    /**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                if ($k == 'bind_id')
                {
                    $columns['search_bind_name'] = $v['label'];
                }
                else
                    $columns[$k] = $v['label'];
            }
        }

        return $columns;
    }



}
