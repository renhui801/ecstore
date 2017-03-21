<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * 这个类实现finder操作的方法
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package aftersales.lib
 */
class aftersales_finder_return_product
{
	/**
	 * @var 定义方法名称的变量
	 */
    public $detail_basic = '基本信息';
    
	/**
	 * @var 所有售后的状态
	 */
    private $arr_status = array();
    
    /**
     * 构造方法，定义全局变量app和状态值
     * @param object app 类
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
        $this->arr_status = array(
            '1' => app::get('aftersales')->_('申请中'),
            '2' => app::get('aftersales')->_('审核中'),
            '3' => app::get('aftersales')->_('接受申请'),
            '4' => app::get('aftersales')->_('完成'),
            '5' => app::get('aftersales')->_('拒绝'),
        );
    }
    
   	/**
   	 * finder的下拉详细页面
   	 * @param sring 售后序号
   	 * @return string 售后详细的内容
   	 */
    public function detail_basic($return_id)
    {
        $render = $this->app->render();
        $obj_return_product = $this->app->model('return_product');
        $arr_return_product = $obj_return_product->dump($return_id);
        if ($arr_return_product['comment'])
            $arr_return_product['comment'] = unserialize($arr_return_product['comment']);
        if ($arr_return_product['product_data'])
            $arr_return_product['product_data'] = unserialize($arr_return_product['product_data']);
        if ($arr_return_product['status'])
        {
			$arr_return_product['status_code'] = $arr_return_product['status'];
			$arr_return_product['status'] = $this->arr_status[$arr_return_product['status']];
            
        }
        if ($_GET['status'])
            $arr_return_product['return_status'] = $_GET['status'];
        else
            $arr_return_product['return_status'] = '1';
		
        $render->pagedata['info'] = $arr_return_product;

        //判断是否对接OCS
        $obj_b2c_shop = app::get('b2c')->model('shop');

        //ajx 添加ecos.ocs接口
        $node_type = array('ecos.ome','ecos.ocs');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type|in'=>$node_type));
        if($cnt>0){
            $render->pagedata['showBtn'] = false;
        }else{
            $render->pagedata['showBtn'] = true;
        }
        
        return $render->fetch('admin/return_product/detail.html');
    }
    
    /**
     * @var 定义finder操作按钮的方法名称变量
     */
    public $column_editbutton = '操作';
    public $column_editbutton_order = '1';
    /**
     * finder操作按钮的方法实现
     * @param array dump数据库该行的信息
     * @return string 操作链接的html信息
     */
    public function column_editbutton($row)
    {
        //判断是否对接OCS
        $obj_b2c_shop = app::get('b2c')->model('shop');

        //ajx 添加ecos.ocs接口
        $node_type=array('ecos.ome','ecos.ocs');
        $cnt = $obj_b2c_shop->count(array('status'=>'bind','node_type|in'=>$node_type));
        if($cnt>0) return '';

        $render = $this->app->render();
        $arr = array(
            'app'=>$_GET['app'],
            'ctl'=>$_GET['ctl'],
            'act'=>$_GET['act'],
            'action'=>'detail',
            'finder_name'=>$_GET['_finder']['finder_id'],
            'finder_id'=>$_GET['_finder']['finder_id'],
            'finderview'=>'detail_basic',
        );
        
        $link = 'index.php?'.utils::http_build_query($arr).'&id='.$row['return_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'];
        
        $status_audit = array(
            'id'=>'x-return-status_'.$row['return_id'].'_2',
            'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
            'target'=>'request::{url:\''.$link.'&status=2\',data:\'return_id='.$row['return_id'].'&status=2\'}',
            'comment'=>'false',
            'label'=>app::get('aftersales')->_('审核中'),
        );

        $status_accept = array(
            'id'=>'x-return-status_'.$row['return_id'].'_3',
            'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
            'target'=>'request::{url:\''.$link.'&status=3\',data:\'return_id='.$row['return_id'].'&status=3\'}',
            'comment'=>'true',
            'label'=>app::get('aftersales')->_('接受申请'),
        );

        $status_finish = array(
            'id'=>'x-return-status_'.$row['return_id'].'_4',
            'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
            'target'=>'request::{url:\''.$link.'&status=4\',data:\'return_id='.$row['return_id'].'&status=4\'}',
            'comment'=>'true',
            'label'=>app::get('aftersales')->_('完成'),
        );

        $status_reduce = array(
            'id'=>'x-return-status_'.$row['return_id'].'_5',
            'href'=>"index.php?app=aftersales&ctl=admin_returnproduct&act=save",
            'target'=>'request::{url:\''.$link.'&status=5\',data:\'return_id='.$row['return_id'].'&status=5\'}',
            'comment'=>'true',
            'label'=>app::get('aftersales')->_('拒绝'),
        );

        if($row['status']==1){
            $arr_links = array(
                $status_audit,
                $status_accept,
                $status_finish,
                $status_reduce
            );
            $can_process_flag = true;
        }elseif($row['status']==2){
            $arr_links = array(
                // $status_audit,
                $status_accept,
                $status_finish,
                $status_reduce
            );
            $can_process_flag = true;
        }elseif($row['status']==3){
            $arr_links = array(
                // $status_audit,
                // $status_accept,
                $status_finish,
                $status_reduce
            );
            $can_process_flag = true;
        }elseif($row['status']==4){
            $arr_links = array(
                // $status_audit,
                // $status_accept,
                // $status_finish,
                // $status_reduce
            );
            $can_process_flag = false;
        }elseif($row['status']==5){
            $arr_links = array(
                // $status_audit,
                // $status_accept,
                // $status_finish,
                // $status_reduce
            );
            $can_process_flag = false;
        }

        $render->pagedata['arr_links'] = $arr_links;
        $render->pagedata['can_process'] = $can_process_flag;
        return $render->fetch('admin/actions.html');
    }
}

 
