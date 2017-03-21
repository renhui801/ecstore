<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_comment_goods_point extends dbeav_model{

    function get_type_name($type_id){
        $comment_goods_type = $this->app->model('comment_goods_type');
        $sdf = $comment_goods_type->dump($type_id);
        return $sdf['name'];
    }

    /*
        设置评分状态 addon['display']
    */
    function set_status($comment_id=0,$status='false'){
        if(!$comment_id) return ;
        $sdf['display'] = $status;
        $this->update($sdf,array('comment_id' => $comment_id));
    }
    /*
    商品各类总分,平均分
    return array
    params goods_id
    */
    function get_goods_point($goods_id=null){
        if(!$goods_id) return null;
        $objType = $this->app->model('comment_goods_type');
        $row = $objType->getList('*');
        foreach((array)$row as $val){
            $data = $this->get_type_point($val['type_id'],$goods_id);
            $data['type_name'] = $val['name'];
            $aData[] = $data;
        }
        return $aData;
    }

    function get_type_point($type_id,$goods_id){
        $row = $this->getList('sum(goods_point) as count_point ,count(*) as count_num',array('goods_id' => $goods_id,'type_id' => $type_id,'display'=>'true'));
        $num = $row[0]['count_point'];
        $total = $row[0]['count_num'];
        if($num == 0 || $total==0) $data['avg'] = 0;
        else $data['avg'] =  number_format((float)$num/$total,1);
        $data['total'] = $num;
        return $data;

    }

    /*
    作为商品总分的类型ID
    return array
    params goods_id
    */
    function totalType(){
        $objType = $this->app->model('comment_goods_type');
        $row = $objType->getList('*');
        $type_id = 1;
        foreach((array)$row as $val){
           $addon = unserialize($val['addon']);
           if($addon['is_total_point'] == 'on') $type_id = $val['type_id'];
        }
        return $type_id;
    }
    /*
    单条评论商品单一类型评分
    return array
    params goods_id
    */

    function get_comment_point($comment_id=null){
        if(!$comment_id) return null;
        $type_id = $this->totalType();
        $row = $this->getList('goods_point',array('comment_id' => $comment_id,'type_id' => $type_id));
        if($row) return $this->star_class($row[0]['goods_point']);
        return null;
    }

    /*
     * 会员中批量获取单条评论评分
     * */
    public function get_comment_point_arr($comment_ids){
        if(!$comment_ids) return null;
        $type_id = $this->totalType();
        $data = $this->getList('goods_point,comment_id',array('comment_id' => $comment_ids,'type_id' => $type_id));
        foreach($data as $key=>$row){
            $return[$row['comment_id']] = $this->star_class($row['goods_point']);
        }
        if($return) return $return;
        return null;
    }
     /*
    商品单一类型评分
    return array
    params goods_id
    */
    function get_single_point($goods_id=null){
        if(!$goods_id) return null;
        $type_id = $this->totalType();
        $_singlepoint = $this->get_type_point($type_id,$goods_id);
        $_singlepoint['avg_num'] = $_singlepoint['avg'];
        if(!$_singlepoint) return null;
        else{
            $_singlepoint['avg'] = $this->star_class($_singlepoint['avg']);
            return $_singlepoint;
        }
    }
     /*
    批量商品单一类型评分
    return array
    params array gids
    */
    function get_single_point_arr($gids=null){
        if(!is_array($gids)){
            $_singlepoint = $this->get_single_point($gids);
            return $_singlepoint;
        }
        $type_id = $this->totalType();

        //$data = $this->getList('goods_id,sum(goods_point) as count_point ,count(*) as count_num',array('goods_id' => $gids,'type_id' => $type_id,'display'=>'true'));
        $str_gids = implode(',',$gids);
        $sql = 'select goods_id,sum(goods_point) as count_point ,count(*) as count_num from sdb_b2c_comment_goods_point where display="true" and type_id='.$type_id.' and goods_id in ('.$str_gids.') group by goods_id';
        $data = $this->db->select($sql);
        foreach($data as $row){
            $gid = $row['goods_id'];
            if($row['count_point'] == 0 || $row['count_num'] ==0){
                $_singlepoint['avg'] = 0;
            }else{
                $_singlepoint['avg'] =  number_format((float)$row['count_point']/$row['count_num'],1);
                $sdf[$gid]['total'] = $row['count_point'];
            }

            $sdf[$gid]['avg_num'] = $_singlepoint['avg'];
            if($sdf[$gid]['avg_num']){
                $sdf[$gid]['avg'] = $this->star_class($_singlepoint['avg']);
            }
            $sdf[$gid]['comments_count'] =  $row['count_num'];
        }
        return $sdf;
    }

    function star_class($avg){
        $a = $avg;
        $t = round($avg);
        if( $a==$t ) {
            $r = floor($a);
        }else {
            switch( $t>$a ) {
                case true:
                if( $t-$a!=0.5 ) {
                    $r = $t;break;
                }
                case false:
                $r = floor($a).'_';
            }
        }
        return $r;
    }

    function get_point_nums($gid=null)
    {
        if(!$gid) return 0;
        $comment_goods_type_sql  = 'select count(*) as count from sdb_b2c_comment_goods_type';
        $comment_goods_point_sql = 'select count(*) as count from sdb_b2c_comment_goods_point where display="true" and goods_id='.$gid;
        $type_num = $this->db->selectrow($comment_goods_type_sql);
        $point_num = $this->db->selectrow($comment_goods_point_sql);
        return ceil($point_num['count']/$type_num['count']);
    }

}
