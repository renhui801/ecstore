<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_ctl_shoprelation extends desktop_controller{

    var $workground = 'desktop_ctl_shoprelation';

    function index($method='apply', $app_id='b2c', $callback='', $api_url='', $user_id='', $user_name='', $api_v=''){
        $this->Certi = base_certificate::get('certificate_id');
        $_node_token = base_shopnode::get('token',$app_id);
        $this->Token = $_node_token ? $_node_token : base_certificate::get('token');;
        $this->Node_id = base_shopnode::node_id($app_id);
        $token = $this->Token;
        $sess_id = kernel::single('base_session')->sess_id();
        $apply['certi_id'] = $this->Certi;
        if ($this->Node_id)
            $apply['node_idnode_id'] = $this->Node_id;
        $apply['sess_id'] = $sess_id;
        $str   = '';
        ksort($apply);
        foreach($apply as $key => $value){
            $str.=$value;
        }
        $apply['certi_ac'] = md5($str.$token);

        
        if ($method == 'apply')
        {
            if ($apply['node_idnode_id'])
                $_url = MATRIX_RELATION_URL . '?source=apply&certi_id='.$apply['certi_id'].'&node_id=' . $apply['node_idnode_id'] . '&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback=' . $callback . '&api_url=' . $api_url . '&op_id=' . $user_id . '&op_user=' . $user_name . '&api_v=' . $api_v;
            else
                 $_url =MATRIX_RELATION_URL . '?source=apply&certi_id='.$apply['certi_id'].'&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback=' . $callback . '&api_url=' . $api_url . '&op_id=' . $user_id . '&op_user=' . $user_name . '&api_v=' . $api_v;
        }
        elseif ($method == 'accept')
        {
            if ($apply['node_idnode_id'])
                $_url = MATRIX_RELATION_URL . '?source=accept&certi_id='.$apply['certi_id'].'&node_id=' . $apply['node_idnode_id'] . '&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback=' . $callback . '&api_url=' . $api_url . '&op_id=' . $user_id . '&op_user=' . $user_name . '&api_v=' . $api_v;
            else
                $_url = MATRIX_RELATION_URL . '?source=accept&certi_id='.$apply['certi_id'].'&sess_id='.$apply['sess_id'].'&certi_ac='.$apply['certi_ac'].'&callback=' . $callback . '&api_url=' . $api_url . '&op_id=' . $user_id . '&op_user=' . $user_name . '&api_v=' . $api_v;
        }
        else
        {
            $this->pagedata['_PAGE_CONTENT'] = "";
        }

        // 私有矩阵iframe url
        if('private' == app::get('system')->getConf('system.matrix.set')){
            $params=array(
                'source'=>$method,
                'node_id'=>$apply['node_idnode_id'],
                'callback'=>$callback,
                );
            $sign = kernel::single('system_shopmatrix')->get_sign($params,$apply['node_idnode_id']);
            $iframe_url = kernel::single('system_shopmatrix')->get_iframe_url($this->Node_id);
            if($method == 'apply'){
                if($apply['node_idnode_id']){
                    $_url = $iframe_url.'?source=apply&node_id='.$apply['node_idnode_id'].'&callback='.$callback.'&sign='.$sign;
                }

            }elseif($method == 'accept'){
                if($apply['node_idnode_id']){
                    $_url = $iframe_url.'?source=accept&node_id='.$apply['node_idnode_id'].'&callback='.$callback.'&sign='.$sign;
                }

            }else{

                $this->pagedata['_PAGE_CONTENT'] = "";
            }
        }

        if($_url){
           echo "<script>new Dialog('$_url',{iframe:true,title:'TITLE',width:.8,height:.8});</script>";
        }
        
        //$this->page();
    }



}
