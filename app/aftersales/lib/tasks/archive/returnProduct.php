<?php
class aftersales_tasks_archive_returnProduct extends base_task_abstract implements base_interface_task{

    // 每个执行100条信息
    var $limit = 100;

    public function __construct(){
        $this->objReturnProduct = app::get('aftersales')->model('return_product');
        $this->objReturnProductArchive = app::get('aftersales')->model('archive_return_product');
    }

    public function exec($params=null){

        $filter = array(
            'status' => array('4','5'),
            'add_time|lthan' => strtotime('-3 month'),
        );

        $offset = 0;
        while( $listFlag = $this->get_return_ids($limit_return_ids, $filter, $offset)  ){
            $offset++;
            $this->archive($limit_return_ids);
        }
        logger::info("归档创建时间小于 ".date('Y-m-d H:i:s',$filter['add_time|lthan'])." 的售后单");

    }

    /**
     * 分页获取售后id
     * @param  array $limit_return_ids 引用获取一页售后单号
     * @param  array $filter          售后单过滤条件
     * @param  int $offset          页数
     * @return bool                  [description]
     */
    function get_return_ids(&$limit_return_ids , $filter, $offset){

        if( !$new_return_ids = $this->objReturnProduct->getList('return_id', $filter, $offset*$this->limit, $this->limit) ){
            return false;
        }

        $limit_return_ids = array();
        foreach($new_return_ids as $v){
            $limit_return_ids[] = $v['return_id'];
        }
        return true;

    }

    public function archive($return_ids){

        $db = kernel::database();
        $transaction_status = $db->beginTransaction();
        $insert_error_code = 0;
        $delete_error_code = 0;
        // 归档数据到新表
        try {
            $this->op_archive($return_ids, false);
        } catch ( Exception $e ) {
            $insert_error_code = $e->getCode();
        }

        if($insert_error_code == 30002){
            $db->rollback();
        } else {
            // 删除老数据
            try {
                $this->op_archive($return_ids, true);
            } catch ( Exception $e ) {
                $delete_error_code = $e->getCode();
            }

            if($delete_error_code == 30003){
                $db->rollback();
            } else {
                $db->commit($transaction_status);
            }
        }

    }

    function op_archive($return_ids, $delete=false){
        // 售后单
        $this->return_product($return_ids, $delete);
    }

    public function return_product($return_ids=null, $delete=false){

        if($delete){
            if( !$this->objReturnProduct->delete( array('return_id'=>$return_ids) ) ){
                throw new Exception("delete archive return_product failue", 30003);
            }
        }else{
            $returnProduct = $this->objReturnProduct->getList( '*', array('return_id'=>$return_ids) );
            foreach($returnProduct as $v){
                if( !$this->objReturnProductArchive->insert($v) ){
                    throw new Exception("insert archive return_product  failue", 30002);
                }
            }
        }

    }

}