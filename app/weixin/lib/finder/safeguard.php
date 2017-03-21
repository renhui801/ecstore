<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class weixin_finder_safeguard{

    public function __construct($app)
    {
        $this->app = $app;
    }
     

    var $column_edit_order = 10;
    public function detail_basic($id){
        $render = $this->app->render();
        $row = app::get('weixin')->model('safeguard')->getRow('*',array('id'=>$id));
        $data = $row;
        $bind = app::get('weixin')->model('bind')->getRow('name',array('appid'=>$row['appid']));
        $data['bind_weixin_name'] = $bind['name'];

        $payments = app::get('ectools')->model('payments')->getRow('payment_id',array('trade_no'=>$row['transid']));
        $orderBills = app::get('ectools')->model('order_bills')->getRow('rel_id',array('bill_id'=>$payments['payment_id']));
        $ordersData = app::get('b2c')->model('orders')->getRow('status',array('order_id'=>$orderBills['rel_id']));
        $data['order_status'] = $ordersData['status'];
        $data['order_id'] = $orderBills['rel_id'];

        $ordersItems = app::get('b2c')->model('order_items')->getList('bn,name,nums,product_id',array('order_id'=>$orderBills['rel_id']));
        foreach($ordersItems as $k=>$row){
            $link = app::get('site')->router()->gen_url( array( 'app'=>'b2c','ctl'=>'site_product', 'full'=>1, 'arg0'=>$row['product_id'] ) );
            $ordersItems[$k]['link'] = $link;
        }
        $render->pagedata['order_items'] = $ordersItems;

        $render->pagedata['data'] = $data;
        return $render->fetch('admin/business/safeguard_detail.html');
    }

    var $column_edit = '编辑';
    function column_edit($row){
        if( $row['status'] != '1' ){
            return '已接受维权';
        }else{
            $target = '{onComplete:function(){if (finderGroup&&finderGroup[\'' . $_GET['_finder']['finder_id'] . '\']) finderGroup[\'' . $_GET['_finder']['finder_id'] . '\'].refresh();}}';
            $return = ' <a target="'.$target.'" href="index.php?app=weixin&ctl=admin_business_safeguard&act=updatefeedback&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['id'].'">'.app::get('b2c')->_('接受维权').'</a>';
            return $return;
        }
    }

}
