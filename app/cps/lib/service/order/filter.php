<?php
class cps_service_order_filter{
    function extend_filter(&$filter){

        if($filter['refer_id'] || $filter['refer_url'] || $filter['refer_time'] || $filter['c_refer_id'] || $filter['c_refer_url'] || $filter['c_refer_time'])
        {
            $cpslink = kernel::single('cps_mdl_linklog');
            $bd_filter['target_type'] = 'order';
            $bd_filter['refer_id|has'] = $filter['refer_id'];
            $bd_filter['refer_url|has'] = $filter['refer_url'];
            $bd_filter['refer_time'] = $filter['refer_time'];
            $bd_filter['c_refer_id|has'] = $filter['c_refer_id'];
            $bd_filter['c_refer_url|has'] = $filter['c_refer_url'];
            $bd_filter['c_refer_time'] = $filter['c_refer_time'];

            foreach($bd_filter as $k=>$v){
                if(empty($v)){
                    unset($bd_filter[$k]);
                }
            }
            $row = $cpslink->getList('target_id',$bd_filter);

            if($filter['order_id'] == -1){
                unset($filter['order_id']);
                $filter['order_id'] = array();
            }

            if(empty($filter['order_id'])) $filter['order_id'] = array();

            foreach((array)$row as $v){
                $filter['order_id'][] = $v['target_id'];
            }
            if(empty($filter['order_id'])){
                $filter['order_id'] = -1;
            }

        }

    }
}
