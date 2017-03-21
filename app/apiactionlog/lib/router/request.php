<?php
class apiactionlog_router_request{
    
    function re_request($apilog_ids){

        $mdl_apilog = app::get('apiactionlog')->model('apilog');
        if(is_array($apilog_ids)){
            foreach($apilog_ids as $k=>$id){
                $row = $mdl_apilog->getList('*',array('apilog'=>$id,'status|in'=>array('fail','sending'),'api_type'=>'request'));
                return $this->_request($row[0]);
            }
        }else{
                $row = $mdl_apilog->getList('*',array('apilog'=>$apilog_ids,'status|in'=>array('fail','sending'),'api_type'=>'request'));
                return $this->_request($row[0]);
        }
    }

    function _request($row){
        $method = $row['worker'];
        $params = unserialize($row['params']);
        $rpc_id = $row['apilog'];
        $calltime = $row['calltime'];
        $msg = '手动重试';

        $obj_shop = app::get('b2c')->model('shop');
        $mdl_apilog = app::get('apiactionlog')->model('apilog');
        $obj_shop_filter = array('status' => 'bind');
        $arr_shops = $obj_shop->getList('*',$obj_shop_filter);

        if($params){
            $return = $mdl_apilog->db->exec("UPDATE sdb_apiactionlog_apilog SET retry=retry+1,last_modified=".time().",status='sending',msg='".$row['msg'].";".$msg."' WHERE apilog='".$rpc_id."'");
            $shop_node_type = $params["node_type"];
            $shop_node_id = $params['to_node_id'];
        }

        if($arr_shops){
            foreach($arr_shops as $shop){
                if($shop['node_type'] ==$shop_node_type &&  $shop['node_id'] == $shop_node_id){
                    $result = app::get('b2c')->matrix()->call($method,$params,$rpc_id."-".$calltime);
                }
            }
        }
        if($return){
            return array('task_name'=>$row['task_name'], 'status'=>'succ');
        }else{
            return array('task_name'=>$row['task_name'], 'status'=>'fail');
        }

    }
}
