<?php
/*
 * 商品搜索
 * */
class searchrule_type_goods
{

    //搜索服务
    var $search_server = null;

    /*
     * 获取ID
     * @queryArr array 搜索帅选条件
     * @res array('result'=>array('id集合'),'total'=>总数) 返回数据
     * */
    public function getListId($filter){
        $filter['filter'] = $this->filter_decode($filter['filter']);
        $res = $this->search_server->select($filter);
        return $res ? $res : false;
    }

    /*
     * 获取商品列表
     * @queryArr array  条件
     * @res array 返回数据
     * */
    public function getList($cols,$filter,$offset,$limit,$orderType,&$total=false){
        $queryArr['search_keywords'] = $this->pre_filter($filter);
        if(empty($queryArr['search_keywords'])){
            $model_filter = $filter;
            $model_filter['search'] = false;
            $data = app::get('b2c')->model('goods')->getList($cols,$model_filter,$offset,$limit,$orderType);
            return $data;
        }
        $queryArr['filter'] = $filter;
        $queryArr['offset'] = $offset <= 0 ? 0 : $offset;
        $queryArr['limit'] = $limit==-1 ? 20 : $limit;
        $queryArr['orderBy'] = str_replace('goods_id','id',$orderType);
        $res = $this->getListId($queryArr);
        if($res['data']){
            foreach($res['data'] as $row){
                $model_filter['goods_id'][] = $row['id'];
            }
            $model_filter['search'] = false;
            $data = app::get('b2c')->model('goods')->getList($cols,$model_filter);
            $sortarr = array_flip($model_filter['goods_id']);
            foreach($data as $row){
                if($filter['is_buildexcerpts'] == 'true'){
                    $row['name'] = $this->BuildExcerpts($row['name'],$queryArr['search_keywords']);
                }
                $k = $sortarr[$row['goods_id']];
                $return[$k] = $row;
            }
            ksort($return);
        }else{
            $return = $res['data'];
        }
        $total = isset($res['total_found']) ? $res['total_found'] : false;
        return $return;
    }

    /*
     *根据搜索条件获取分类id
     * */
    public function get_cat($queryArr){
        $queryArr['search_keywords'] = $this->pre_filter($queryArr);
        if($queryArr['filter']['cat_id']){
            return null;
        }
        $queryArr['groupBy'] = 'cat_id';
        $res = $this->getListId($queryArr);
        if($res){
            foreach($res['data'] as $row){
                $cat[$row['cat_id']] = $row['@count'];
            }
        }else{
            return null;
        }
        return $cat;
    }

    public function BuildExcerpts($text,$word,$opts){
        $segment = search_core::segment();
        $words = $segment->split_words($word);
        if(empty($opts)){
            $opts=array(
                'before_match'=>'<font color="red">',
                'after_match'=>'</font>'
            );
        }
        foreach($words as $row){
            $opts_str = $opts['before_match'].$row.$opts['after_match'];
            $text = str_replace($row,$opts_str,$text);
        }
        return $text;
    }
    /*
     * 更新索引
     * @queryArr array  条件
     * @return bool
     * */
    public function update($queryArr=array(),$where=array()){
        if($where['goods_id']){
            $where['id'] = $where['goods_id'];
            unset($where['goods_id']);
        }
        if($queryArr['marketable'] && $queryArr['marketable'] == 'false'){
            $queryArr['marketable'] = 0;
        }else{
            $queryArr['marketable'] = 1;
        }
        $this->search_server->update($queryArr,$where);
    }

    public function insert($queryArr){
        $this->search_server->insert($queryArr);
    }


    public function filter_decode($filter){
        if($filter['goods_id']){
            $filter['id'] = $filter['goods_id'];
            unset($filter['goods_id']);
        }
        if($filter['marketable'] == 'false'){
            $filter['marketable'] = 0;
        }elseif($filter['marketable'] == 'true'){
            $filter['marketable'] = 1;
        }
        $filter['disabled'] = 0;
        return $filter;
    }
    public function pre_filter(&$filter){
        $filter = kernel::single('b2c_goods_filter')->base_filter($filter);
        if(!empty($filter['search_keywords'])){
            $search_keywords = $filter['search_keywords'][0];
            unset($filter['search_keywords']);
        }

        if(!empty($filter['cat_id'])){
            $filter['cats'] = $filter['cat_id'];
            unset($filter['cat_id']);
        }
        if($filter['is_store'] == 'on'){
            $filter['is_store'] = '1';
        }

        if($filter['tag']){
            $filter['tag_id'] = $filter['tag'];
        }
        $describe_field = array('cats','name','brand_name','brand_keywords','goods_type','brief','bn','keyword','product_bn');
        foreach($filter as $col=>$val){
            $cols = explode('|',$col);
            if(in_array($cols[0],$describe_field)){//选择多个分类
                if($cols[0] == 'cats' && is_array($val)){
                    $search_keywords .= ' (@cats '.implode(') | (@cats ',$val).') ';
                    unset($filter[$col]);
                    continue;
                }
                if($cols[0] == 'goods_type' && is_array($val)){//暂时先这样
                    $search_keywords .= ' (@goods_type '.implode(') | (@goods_type ',$val).') ';
                    unset($filter[$col]);
                    continue;
                }
                if(is_array($val)){
                    $val_str = implode(' ',$val);
                }else{
                    $val_str = $val;
                }
                $search_keywords .= ' @'.$cols[0]. ' '.$val_str;
                unset($filter[$col]);
            }
        }
        return $search_keywords;
    }
}//End Class
