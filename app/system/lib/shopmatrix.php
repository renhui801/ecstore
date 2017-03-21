<?php
class system_shopmatrix{
    
    function get_iframe_url($node_id){
        $matrix_mdl = app::get('system')->model('matrixset');
        $list =  $matrix_mdl->getList("iframe_url",array('node_id'=>$node_id,'status'=>'active'));
        return $list[0]['iframe_url'];
    }

    function get_api_url($node_id){
        $matrix_mdl = app::get('system')->model('matrixset');
        $list =  $matrix_mdl->getList("api_url",array('node_id'=>$node_id,'status'=>'active'));
        return $list[0]['api_url'];
    }


    function get_token($node_id){
        $matrix_mdl = app::get('system')->model('matrixset');
        $list =  $matrix_mdl->getList("token",array('node_id'=>$node_id,'status'=>'active'));
        return $list[0]['token'];
    }

    function get_sign($params,$node_id=""){
        if(!$node_id){
            $node_id = base_shopnode::node_id('b2c');
        }
        $token = $this->get_token($node_id);
        if($token){
            return kernel::single('system_request')->gen_sign($params,$token);
        }
        return false;

    }
}
