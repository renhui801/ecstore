<?php
class apiactionlog_mdl_apilog extends dbeav_model{

    function __construct($app){
        parent::__construct($app);
        $this->use_meta();
    }

    function update($data,$filter=array(),$mustUpdate = null){
        if($filter){
            foreach($filter as $key=>$val){
                $where .= " AND ".$key."='".$val."'";
            }
        }
        if($data){
            foreach($data as $k=>$v){
                $set .= $k."='".$v."',";
            }
            $set = substr($set,0,-1);
        }

        $sql = 'UPDATE `'.$this->table_name(1).'` SET '.$set.' WHERE 1 '.$where;
        $result = $this->db->exec($sql);

        return $result;
    }

    function save_data($data){
        if($data){
            $msg_id = $data['msg_id'];
            $log = $this->getList('*',array('msg_id'=>$msg_id));
            if(count($log)>0){
                return $this->update($data,array('msg_id'=>$msg_id,'api_type'=>'response'));
            }else{
                return $this->insert($data);
            }
        }

    }

   # function save(&$data,$api_type="order",$log_type="request"){
   #    $order_api = array(); 

   #    $time = time();
   #    $microtime = utils::microtime();
   #    $rpc_id = str_replace('.','',strval($microtime));
   #    $randval = uniqid('', true);
   #    $rpc_id .= strval($randval);
   #    $rpc_id = md5($rpc_id);
   #    $save_data = array(
   #         'apilog_id'=>$rpc_id,
   #         'original_bn'=>'',
   #         'msg_id'=>'',
   #         'task_name'=>'',
   #         'calltime'=>$time,
   #         'status'=>'',
   #         'worker'=>'',
   #         'params'=>'',
   #         'msg'=>'',
   #         'log_type'=>'',
   #         'api_type'=>'',
   #         'retry'=>'',
   #        );
   #    return $rpc_id;
   # }
}
