<?php
class apiactionlog_ctl_admin_apilog extends desktop_controller{

    var $workground = "apiactionlog.wrokground.apilog";
    function index($status='all', $api_type='request'){
        $base_filter = '';
        $orderby = ' createtime desc ';
        switch($status){
            case 'all':
                $this->title = '所有同步日志';
                break;
            case 'running':
                $this->title = '同步运行中日志';
                $base_filter = array('status'=>'running');
                break;
            case 'success':
                $this->title = '同步成功日志';
                $base_filter = array('status'=>'success');
                break;
            case 'fail':
                $this->title = '同步失败日志';
                $base_filter = array('status'=>'fail','api_type'=>$api_type);
                break;
            case 'sending':
                //kernel::single('ome_sync_api_log')->clean();
                $this->title = '发起中日志';
                $base_filter = array('status'=>'sending');
                break;
        }

        if ($status=='fail' && $api_type=='request'){
            $actions =
                array(
                    array(
                        'label' => '批量重试',
                        'submit' => 'index.php?app=apiactionlog&ctl=admin_apilog&act=re_request',
                        'target' => "refresh",//dialog::{width:550,height:300,title:'批量重试'}",
                    ),
                );
        }

        $params = array(
            'title'=>$this->title,
            'actions'=> $actions,
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag'=>false,
            'use_buildin_recycle'=>false,
            'use_buildin_export'=>false,
            'use_buildin_import'=>false,
            'use_buildin_filter'=>true,
            'orderBy' => $orderby,
        );

        if($base_filter){
            $params['base_filter'] = $base_filter;
        }

        if(!isset($_GET['action'])) {
            $panel = kernel::single('apiactionlog_panel',$this);
            $panel->setId('api_log_finder_top');
            $panel->setTmpl('admin/finder/finder_panel_filter.html');
            $panel->show('apiactionlog_mdl_apilog', $params);
        }

        $this->finder('apiactionlog_mdl_apilog',$params);
    }

    function retry($log_id='', $retry_type='single'){
        if ($retry_type == 'single'){
            $this->pagedata['log_id'] = $log_id;
        }else{
            if (is_array($log_id['log_id'])){
                $this->pagedata['log_id'] = implode("|", $log_id['log_id']);
            }
        }
        $this->pagedata['isSelectedAll'] = $log_id['isSelectedAll'];
        $this->pagedata['retry_type'] = $retry_type;
        $this->display("admin/api/retry.html");
    }

    function re_request($order_no){
        $this->begin();
        if($order_no){
            $apilog_id = $order_no;
        }elseif($_POST){
            $apilog_id = $_POST;
        }

        $request_mdl = kernel::single('apiactionlog_router_request');
        $result = $request_mdl->re_request($apilog_id);
        $this->end($result);
        //echo json_encode($result);
        exit;
    }

    function batch_retry(){
        $this->retry($_POST, 'batch');
    }
}
