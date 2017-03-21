<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_message_comment{

    var $objComment;
    var $type;
    function __construct(&$app){
        $this->app = &$app;
        $this->objComment = $this->app->model('member_comments');
        $this->objComment->type = $this->type;
    }
     function save(&$aData){
         if($this->objComment->save($aData)){
             return true;
         }
         else{
             return false;
         }

     }

     function dump($id){
         $aData = $this->getList('*', array('comment_id' => $id,'for_comment_id' => 'all'));
         if($aData[0]['object_type'] == 'discuss'){
             $goods_point = $this->app->model('comment_goods_point');
             $row = $goods_point->getList('*',array('comment_id' => $id));
             $aData[0]['goods_point'] = $row;
         }
         $goodsInfo = app::get('b2c')->model('goods')->getList('goods_id,name',array('goods_id'=>$aData[0]['type_id']));
         $aData[0]['goods_name'] = $goodsInfo[0]['name'];
         $aData[0]['goods_id'] = $goodsInfo[0]['goods_id'];
         return $aData[0];
     }

     function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        $aData = $this->objComment->getList($cols='*', $filter, $offset, $limit, $orderby);
        $objMember = $this->app->model('members');
        foreach($aData as $key => $val){
            $memberIds[] = $val['author_id'];
        }
        $memberLvData = app::get('b2c')->model('member_lv')->getListAll();
        $memberLvIds = $objMember->getList('member_id,member_lv_id',array('member_id'=>$memberIds));
        foreach($memberLvIds as $row){
            $row['member_lv_name'] = $memberLvData[$row['member_lv_id']]['name'];
            $row['member_lv_logo'] = $memberLvData[$row['member_lv_id']]['lv_logo'];
            $tmpMemberLv[$row['member_id']] = $row;
        }
        $row = array();
        foreach($aData as $key => $val){
            $val['member_lv_name'] = $tmpMemberLv[$val['author_id']]['member_lv_name'];
            $val['member_lv_logo'] = $tmpMemberLv[$val['author_id']]['member_lv_logo'];
            $val['addon'] = unserialize($val['addon']);
            $row[] = $val;
        }
        return $row;
      }
      function count($filter=array()){
        $aData = $this->objComment->count($filter);
        return $aData;
      }

      function count_order_mes($filter=array()){
          $count = $this->objComment->db->selectRow("select count(distinct order_id) count from sdb_b2c_member_comments where adm_read_status = '".$filter['adm_read_status']."' and object_type= '".$filter['object_type']."' and for_comment_id=0");
        return $count['count'];
      }
      function delete($filter=null){
          if($this->objComment->delete($filter)){
              return true;
          }
          else{
              return false;
          }
      }

    function get_reply($comment_id){
        $aData = $this->getList('*',array('for_comment_id' => $comment_id,'display'=>'true'));
        return $aData;
    }

      function setReaded($comment_id){
        $sdf = $this->dump($comment_id);
        $sdf['mem_read_status'] = 'true';
        $this->save($sdf);
    }

}
