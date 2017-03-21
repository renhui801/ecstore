<?php
class b2c_mdl_goods_rate extends dbeav_model{
    
    function __construct($app){
        parent::__construct($app);
    }

    function delete($where,$subSdf = 'delete'){
        if($where){
            foreach($where as $k=>$v){
                $goods_id = $v;
            }
        }
        //$ratelist = $this->db->select('select * from sdb_b2c_goods_rate where goods_1='.$goods_1.' or goods_2='.$goods_2);
        $sql = "delete from sdb_b2c_goods_rate where goods_1=".$goods_id." or goods_2=".$goods_id;
        $this->db->exec($sql,$this->skipModifiedMark);
    }
}
