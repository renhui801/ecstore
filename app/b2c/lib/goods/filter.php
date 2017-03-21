<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_goods_filter extends dbeav_filter{
    var $name = 'B2C商品筛选器';
    function goods_filter(&$filter, &$object){

        if(!is_array($filter) || (isset($filter['goods_id']) && count($filter['goods_id']) == 1) ){
            return $this -> _pre_filter($filter);
        } 
        $filter = utils::addslashes_array($filter);
        //过滤基本的条件
        $filter = $this->base_filter($filter);
        $ObjProducts = $object->app->model('products');
        //分类
        $filter = $this->get_cat_filter($filter,$object);

        /** 下面查询通过商品主键组合查询条件 - left join 相应的表 **/
        //关键字查找到对应的goodsId
        if(isset($filter['search_keywords'][0])){
            if($filter['filter_sql']){
                $filter['filter_sql'] .= ' and '.$object->wFilter($filter['search_keywords'][0]);
            }else{
                $filter['filter_sql'] = $object->wFilter($filter['search_keywords'][0]);
            }
        }
        if(isset($filter['keyword']) && $filter['keyword']) {
            $filter['keywords'] = array($filter['keyword']);
        }
        if(isset($filter['keywords']) && $filter['keywords'] && !in_array('_ANY_',$filter['keywords'])) {
            $keywordsList = $object->getGoodsIdByKeyword($filter['keywords'],$filter['_keyword_search']);
            $keywordsGoods = array();
            foreach($keywordsList as $keyword)
                $keywordsGoods[] = intval($keyword['goods_id']);
            if(!empty($keywordsGoods) && !empty($goods)){
                $keywordsGoods = array_intersect($keywordsGoods, $goods);
                if(empty($keywordsGoods))
                    $goods = array('-1');
                else
                    $goods = $keywordsGoods;
            }else{
                if(!empty($keywordsGoods)){
                    $goods = $keywordsGoods;
                }else{
                    $goods = array('-1');
                }
            }
        }
        unset($filter['keywords']);

        //包含商品bn和货品bn都可以搜索到
        if(isset($filter['bn']) && $filter['bn']){
            $sBn = '';
            if(is_array($filter['bn'])){
                $sBn = trim($filter['bn'][0]);
            }else{
                $sBn = trim($filter['bn']);
            }
            $bnGoodsId = $object->getGoodsIdByBn($sBn,$filter['_bn_search']);

            if(!empty($bnGoodsId) && !empty($goods)){
                $bnGoodsId = array_intersect($bnGoodsId, $goods);
                if(empty($bnGoodsId))
                    $goods = array('-1');
                else
                    $goods = $bnGoodsId;
            }else{
                if(!empty($bnGoodsId)){
                    $goods = $bnGoodsId;
                }else{
                    $goods = array('-1');
                }
            }
            unset( $filter['bn'] );
        }

        //货品编号
        if(isset($filter['barcode']) && $filter['barcode']){
            $goods_id = $ObjProducts->getList('goods_id',array('barcode'=>$filter['barcode']));
            if(isset($goods_id[0]['goods_id'])){
                $filter['goods_id'] = $goods_id[0]['goods_id'];
            }else{
                $filter['goods_id'] = 0;
            }
            unset( $filter['barcode'] );
        }

        //规格筛选 mysql中
        if( count( $filter['spec_value_id'])>0 ){
            $sql = 'SELECT goods_id FROM sdb_b2c_goods_spec_index WHERE spec_value_id IN ( '.implode( ',',$filter['spec_value_id']).' )';
            $sGoodsId = $object->db->select($sql);
            $sgid = array();
            foreach( $sGoodsId as $si )
                $sgid[] = $si['goods_id'];
            if(!empty($goods))
                $sgid = array_intersect( $sgid , $goods);
            if(!empty($sgid)){
                $goods = $sgid;
            }else{
                $goods = array(-1);
            }
        }

        if(isset($goods) && count($goods)>0){
            $filter['goods_id'] = $goods;
        }

        if(isset($filter['name']) && $filter['name']){
            if(is_array($filter['name'])){
                $filter['name']=implode(' ',$filter['name']);
                if($filter['name']){
                    $filter['name|has'] = urldecode($filter['name']);
                }
            }else{
              //后台搜索
              $GLOBALS['search']=$filter['name'];
              $filter['name|has'] = $filter['name'];
            }
            unset($filter['name']);
        }

        if(!$filter['goods_type']){
            $filter['goods_type'] = 'normal';
        }
        //前台商品列表页是否有货
        if($filter['is_store'] == 'on'){
            if($filter['filter_sql']){
                $filter['filter_sql'] .= " and (nostore_sell = '1' OR store >= 0.000001)";
            }else{
                $filter['filter_sql'] = "(nostore_sell = '1' OR store >= 0.000001)";
            }
        }
        foreach($filter as $k=>$v){
            if(!isset($v)) unset($filter[$k]);
        }
        if(is_array($filter['goods_id'])){
            $filter['goods_id'] = array_unique($filter['goods_id']);
        }
        return $filter;
    }

    public function base_filter($filter){
        $filter = $this->_pre_filter($filter);
        //价格
        if(is_numeric($filter['price']) && isset($filter['_price_search'])){
            if($filter['_price_search'] != 'between'){
                if(!$filter['price']) $filter['price'] = 0.000001;
                $filter['price|'.$filter['_price_search']] = number_format($filter['price'],'3','.','');
            }
            if(isset($filter['price_from']) && isset($filter['price_to'])){
                if(!$filter['price_from']) $filter['price_from'] = 0.000001;
                if(!$filter['price_to']) $filter['price_to'] = 0.000001;
                $filter['price_from'] = number_format($filter['price_from'],'3','.','');
                $filter['price_to'] = number_format($filter['price_to'],'3','.','');
                $filter['price|'.$filter['_price_search']]=array($filter['price_from'],$filter['price_to']);
            }
            unset($filter['price']);
        }
        if(isset($filter['price']) && is_array($filter['price'])){
            $filter['pricefrom'] = $filter['price'][0];
            $filter['priceto']   = $filter['price'][1];
            unset($filter['price']);
        }
        if($filter['priceto'] || $filter['pricefrom']){
            if(!$filter['pricefrom']) $filter['pricefrom'] = 0.000001;
            if(!$filter['priceto']) $filter['priceto'] = 0.000001;
            $filter['price|between'][] = number_format($filter['pricefrom'],'3','.','');
            $filter['price|between'][] = number_format($filter['priceto'],'3','.','');
            unset($filter['pricefrom']);
            unset($filter['priceto']);
        }

        if(is_numeric($filter['store']) && isset($filter['_store_search'])){
            if($filter['_store_search'] != 'between'){
                $filter['store|'.$filter['_store_search']] = $filter['store'];
            }
            if($filter['store_from'] && $filter['store_to']){
                $filter['store|'.$filter['_store_search']]=array($filter['store_from'],$filter['store_to']);
            }
            unset($filter['store']);
        }

        if($filter['searchname']){
           $filter['search_keywords'][0] = $filter['searchname'];
        }
        unset($filter['searchname']);
        return $filter;
    }

    private function get_cat_filter($filter,$object){
        if($filter['cat_id'] || $filter['cat_id'] === 0){
            //如果需要找出分类下的子分类中的商品
            if(!isset($object->__show_goods)){
                $object->__show_goods = $object->app->getConf('system.category.showgoods');
            }
            if(!$object->__show_goods && $filter['cat_id']){
                $oCat = $object->app->model('goods_cat');
                $fcat_id = $filter['cat_id'];
                $aCat = $oCat->getList('cat_path,cat_id',array('cat_id'=>$fcat_id));
                $pathplus='';
                if(count($aCat)){
                    foreach($aCat as $v){
                        $pathplus.=' cat_path LIKE \''
                            .($v['cat_path']).$v['cat_id'].',%\' OR';
                    }
                }
                if($aCat){
                    foreach($object->db->select('SELECT cat_id FROM sdb_b2c_goods_cat WHERE '.$pathplus.' cat_id in ('.implode((array)$filter['cat_id'],' , ').')') as $rows){
                        $aCatid[] = $rows['cat_id'];
                    }
                }
                if(!is_null($aCatid)){
                    $filter['cat_id'] = $aCatid;
                }
            }
        }
        return $filter;
    }

    //过滤挂件中的'_ANY_'参数和参数为空的字段
    private function _pre_filter($filter=array()){
      $is_numeric = array('price','cost','mktprice','store');
      $filter['spec_value_id'] = array();
      foreach($filter as $col=>$val){
        if(is_array($val)){
          foreach($val as $k=>$v){
            if($v == '_ANY_' || $v[0] == '_ANY_' || empty($v)){
              unset($filter[$col][$k]);
            }
          }
        }else{
          if($val == '_ANY_' || (in_array($col,$is_numeric) && !is_numeric($val)) ){
            unset($filter[$col]);
          }
        }
        if( substr($col,0,2) == 's_' ){
            $filter['spec_value_id'] = array_merge($filter[$col],$filter['spec_value_id']);
            unset($filter[$col]);
        }
        if(is_null($filter[$col]) || $filter[$col] === '' || (is_array($filter[$col]) && empty($filter[$col])) ){
          unset($filter[$col]);
        }
      }
      return $filter;
    }
}

