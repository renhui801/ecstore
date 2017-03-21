<?php
/*
 * 联想搜索
 * */
class searchrule_type_associate
{
    //搜索服务
    var $search_server = null;

    public function get_woreds($word){
        $queryArr['search_keywords'] = '^'.$word;
        #$queryArr['cols'] = 'id';
        $queryArr['offset'] = 0;
        $queryArr['limit'] = 10;
        $res = $this->search_server->select($queryArr);
        $return = array();
        if($res){
            foreach($res['data'] as $row ){
                $filter['id'][] =  $row['id'];
            }
            if($filter['id']){
                $words_data = app::get('search')->model('associate')->getList('words',$filter);
                foreach($words_data as $row){
                    $return[] =  $row['words'];
                }
            }
        }
        return $return;
    }
}

