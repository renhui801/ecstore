<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class aftersales_ctl_admin_returnproduct extends desktop_controller{
    // public $workground = 'ectools_ctl_admin_order';
    var $certcheck = false;

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
        $this->arr_status = array(
            '1' => app::get('aftersales')->_('申请中'),
            '2' => app::get('aftersales')->_('审核中'),
            '3' => app::get('aftersales')->_('接受申请'),
            '4' => app::get('aftersales')->_('完成'),
            '5' => app::get('aftersales')->_('拒绝'),
        );
    }

    public function index()
    {
        $this->workground = 'ectools.wrokground.order';
        if($_GET['action'] == 'export') $this->_end_message = '导出售后服务申请';
        $this->finder('aftersales_mdl_return_product',array(
            'title'=>app::get('aftersales')->_('售后服务管理'),
            'actions'=>array(
                        ),
            'use_buildin_set_tag'=>true,
            'use_buildin_recycle'=>false,
            'use_buildin_filter'=>true,
            'use_view_tab'=>true,
            ));
    }

    /**
     * 桌面订单相信汇总显示
     * @param null
     * @return null
     */
    public function _views(){
        $mdl_aftersales = $this->app->model('return_product');
        $sub_menu = array(
            1=>array('label'=>app::get('aftersales')->_('审核中'),'optional'=>false,'filter'=>array('status'=>2,'disabled'=>'false')),
            2=>array('label'=>app::get('aftersales')->_('接受申请'),'optional'=>false,'filter'=>array('status'=>3,'disabled'=>'false')),
            3=>array('label'=>app::get('aftersales')->_('完成'),'optional'=>false,'filter'=>array('status'=>4,'disabled'=>'false')),
            4=>array('label'=>app::get('aftersales')->_('拒绝'),'optional'=>false,'filter'=>array('status'=>5,'disabled'=>'false')),
            5=>array('label'=>app::get('aftersales')->_('全部'),'optional'=>false,'filter'=>array('disabled'=>'false')),
        );

        if(isset($_GET['optional_view'])) $sub_menu[$_GET['optional_view']]['optional'] = false;

        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array(),$v['filter']);
                }else{
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                if($k==$_GET['view']){
                    $show_menu[$k]['newcount'] = true;
                    $show_menu[$k]['addon'] = $mdl_aftersales->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=aftersales&ctl=admin_returnproduct&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }

        return $show_menu;
    }

    public function save()
    {
        $rp = $this->app->model('return_product');
        $obj_return_policy = kernel::single('aftersales_data_return_policy');

        $return_id = $_POST['return_id'];
        $status = $_POST['status'];
        $sdf = array(
            'return_id' => $return_id,
            'status' => $status,
        );
        $this->pagedata['return_status'] = $obj_return_policy->change_status($sdf);
        if ($this->pagedata['return_status'])
            $this->pagedata['return_status'] = $this->arr_status[$this->pagedata['return_status']];

        $obj_aftersales = kernel::servicelist("api.aftersales.request");
        foreach ($obj_aftersales as $obj_request)
        {
            $obj_request->send_update_request($sdf);
        }

        $this->display('admin/return_product/return_status.html');
    }

    public function file_download($return_id)
    {
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $obj_return_policy->file_download($return_id);
    }

    public function send_comment()
    {
        $rp = $this->app->model('return_product');

        $return_id = $_POST['return_id'];
        $comment = $_POST['comment'];
        $arr_data = array(
            'return_id' => $return_id,
            'comment' => $comment,
        );

        $this->begin();
        if($rp->send_comment($arr_data))
        {
            $this->end(true, app::get('aftersales')->_('发送成功！'));
        }
        else
        {
            //trigger_error(__('发送失败'),E_USER_ERROR);
            $this->end(false, app::get('aftersales')->_('发送失败！'));
        }
    }

    public function settings()
    {
        $this->workground = 'site.wrokground.theme';
        if (!$_POST)
        {
            $this->pagedata['return_product']['comment'] = app::get('aftersales')->getConf('site.return_product_comment');
            $this->page('admin/setting/return_product.html');
        }
        else
        {
            $this->begin('index.php?app=aftersales&ctl=admin_returnproduct&act=settings');

            app::get('aftersales')->setConf('site.return_product_comment', $_POST['conmment']);

            $this->end(true, app::get('aftersales')->_("设置成功！"));
        }
    }
}
