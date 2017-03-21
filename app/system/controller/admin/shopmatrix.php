<?php
class system_ctl_admin_shopmatrix extends desktop_controller{

    var $workground = 'system.workground.setting';
    function __construct($app){
        parent::__construct($app);
        //header("cache-control: no-store, no-cache, must-revalidate"); 
    }

    function index(){
        $matrixtype = app::get('system')->getConf('system.matrix.set');
        if(!$matrixtype){
            $this->pagedata['ck']='public';
        }else{
            $this->pagedata['ck'] = $matrixtype;
        }
        $this->pagedata['node_id']=base_shopnode::node_id('b2c');
        $this->page('admin/shopmatrix.html');
    }

    function save_matrix(){
        
        $this->begin();
        $get_matrixtype = app::get('system')->getConf('system.matrix.set');
        $matrix = app::get('system')->model('matrixset');
        $shop = app::get('b2c')->model('shop');
        $post = $_POST;
        $matrixtype = $post['matrixtype'];
        unset($post['matrixtype']);

        //查看是否存在绑定关系，如果存在提示解除
        $shoplist = $shop->getList('*',array('status'=>'bind'));
        if($get_matrixtype != $matrixtype && count($shoplist)>0){
            //$this->begin('index.php?app=b2c&ctl=admin_shoprelation&act=index');
            $this->end(false,app::get('system')->_("如果需要切换通道，请先解除现有的绑定关系"));
        }

        //基础数据
        $post['node_id'] = base_shopnode::node_id('b2c'); 
        $post['node_type'] = "ecos.b2c";
        $post['matrixset_id'] = intval($post['matrixset_id']);
        $api_url = kernel::base_url(1).kernel::url_prefix().'/api';


        //查看私有矩阵是否已经开通过
        $list_matrix = $matrix->getList('*',array('node_id'=>$post['node_id'],'status'=>'active'));
        if($matrixtype == "private"){
            $params = array(
                'node_type'=> $post['node_type'],
                'node_name'=>$post['shopname'],
                'api_url'=>$api_url,
                'token'=>$post['token'],
            );
            $request = kernel::single('system_request');
            $request_result = $request->register($post['api_url'],$params); 
        }

        if($matrixtype == "private" && $request_result){
            app::get('system')->setConf('system.matrix.set','private');
            $post['status'] = 'active';
            $result = $matrix->save($post);
        }else{
            app::get('system')->setConf('system.matrix.set','public');
            $data['status'] = 'dead'; 
            $result = $matrix->update($data,array('status'=>'active','node_id'=>$post['node_id']));
        }
    
        $this->end($result);
    }

    function getmatrix(){
        $matrix = app::get('system')->model('matrixset');
        $list = $matrix->getList('*');
        $node_id = base_shopnode::node_id('b2c'); 
        $node_type = "ecos.b2c";
        foreach($list as $key => $value){
            $arr = array(
                'api_url'=>$value['api_url'],
                'iframe_url'=>$value['iframe_url'],
                'shopname'=>$value['shopname'],
                'token'=>$value['token'],
                'matrixset_id'=>intval($value['matrixset_id']),
            );
        }
        $arr['node_id'] = $node_id;
        $arr['node_type'] = $node_type;

        echo json_encode($arr);
        //echo "{api_url: 1, iframe_url:2, shopname: 'xxxx', node_id: 4, node_type: 'yyy', token: 5}";

    }


}
