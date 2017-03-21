<?php
class b2c_apiv_apis_response_order_order{
    public function __construct($app)
    {
        $this->app = $app;
    }

    private function check_accesstoken($accesstoken, $member_id)
    {
        $_GET['sess_id'] = $accesstoken;
        kernel::single("base_session")->start();
        $userObject = kernel::single('b2c_user_object');
        $id = $userObject->get_member_id();
        if( empty($id) || $member_id != $id ){
            return false;
        }
        return true;
    }

    public function check_cost($params, $service)
    {
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        $mCart = app::get('b2c')->model('cart');
        $aCart = $mCart->get_objects($aData);
        if(count($aCart['object']['goods']) < 1) return array('status'=>null,'message'=>app::get('b2c')->_('当前购物车为空，请添加商品'));
        $sdf_order['cur'] = $params['cur'];
        $sdf_order['shipping_id'] = $params['shipping_id'];
        $sdf_order['is_protect'] = $params['is_protect'];
        $sdf_order['is_tax'] = $params['is_tax'];
        $sdf_order['tax_type'] = $params['tax_type'];
        $sdf_order['payment'] = $params['payment'];
        $sdf_order['area_id'] = $params['area_id'];
        $sdf_order['dis_point'] = $params['dis_point'];
        $sdf_order['member_id'] = $params['member_id'];
        $obj_total = kernel::single('b2c_order_total');
        $cost = $obj_total->payment_detail($this, $aCart, $sdf_order);
        $return = array(
            'cost_item'=>$cost['cost_item'],
            'cost_freight'=>$cost['cost_freight'],
            'cost_protect'=>$cost['cost_protect'],
            'discountPrice'=>$cost['pmt_amount'],
            'cost_payment'=>$cost['cost_payment'],
            'cost_tax'=>$cost['cost_tax'],
            'consumeScore'=>$cost['totalConsumeScore'],
            'totalGainScore'=>$cost['totalGainScore'],
            'total_amount'=>$cost['final_amount']
        );
        return $return;
    }


    /**
     * 根据订单id获取详情
     * @param $order_id
     * return $order_detial
     */

    public function get_wap_order_detail($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        if (!isset($params['order_id']) || !$params['order_id'])
        {
            $msg = app::get('b2c')->_('订单id不能为空，必要参数！');
            return false;
        }
        //获取订单model
        $objOrder = $order = $this->app->model('orders');
        //组织查询条件
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('product_id,name,price,score,nums,item_type',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        //$subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))), 'order_pmt'=>array('*'));
        //获取订单的详细信息数据
        $sdf_order = $objOrder->dump($params['order_id'], '*', $subsdf);
      
        if($sdf_order['member_id']!=$params['member_id']){
            return array('status'=>'false','message'=>app::get('b2c')->_('该会员不存在'));
        }
       
        // 处理收货人地区
        
        $arr_consignee_area = array();
        $arr_consignee_regions = array();
        if (strpos($sdf_order['consignee']['area'], ':') !== false)
        {
            $arr_consignee_area = explode(':', $sdf_order['consignee']['area']);
            if ($arr_consignee_area[1])
            {
                if (strpos($arr_consignee_area[1], '/') !== false)
                {
                    $arr_consignee_regions = explode('/', $arr_consignee_area[1]);
                }
            }

            $sdf_order['consignee']['area'] = (is_array($arr_consignee_regions) && $arr_consignee_regions) ? $arr_consignee_regions[0] . $arr_consignee_regions[1] . $arr_consignee_regions[2] : $sdf_order['consignee']['area'];
        }
        
        $data['order_id']=$sdf_order['order_id'];
        $data['total_amount']=$sdf_order['total_amount'];
        $data['payed']=$sdf_order['payed'];
        $data['createtime']=$sdf_order['createtime'];
        //订单状态
        switch ($sdf_order['status']) {
            case 'active':
                $data['orderStatus']='活动订单';
                break;
            case 'dead':
                $data['orderStatus']='已作废';
                break;
            case 'finish':
                $data['orderStatus']='已完成';
                break;
           
            default:
                break;
        }
        //支付状态
        switch ($sdf_order['pay_status']) {
            case 0:
                $data['payStatus']='未支付';
                break;
            case 1:
                $data['payStatus']='已支付';
                break;
            case 2:
                $data['payStatus']='已付款至到担保方';
                break;
            case 3:
                $data['payStatus']='部分付款';
                break;
            case 4:
                $data['payStatus']='部分退款';
                break;
            case 5:
                $data['payStatus']='全额退款';
                break;
            default:
                break;
        }
        //发货状态
        switch ($sdf_order['ship_status']) {
            case 0:
                $data['shipStatus']='未发货';
                break;
            case 1:
                $data['shipStatus']='已发货';
                break;
            case 2:
                $data['shipStatus']='部分发货';
                break;
            case 3:
                $data['shipStatus']='部分退货';
                break;
            case 4:
                $data['shipStatus']='已退货';
                break;
            default:
                break;
        }
        
        $data['consignee']=$sdf_order['consignee'];
    
        $data['shipping']['shipping_name']=$sdf_order['shipping']['shipping_name'];
        $data['shipping']['cost_shipping']=$sdf_order['shipping']['cost_shipping'];
        $data['shipping']['is_protect']=$sdf_order['shipping']['is_protect'];
       
        $data['payinfo']=$sdf_order['payinfo'];
      
        //发票类型
        if(isset($sdf_order['tax_type']))
        {
            switch ($sdf_order['tax_type']) {
            case 'false':
                $datas['tax_type']='不需发票';
                break;
            case 'personal':
                $datas['tax_type']='个人发票';
                break;
            case 'company':
                $datas['tax_type']='公司发票';
                break;
           
            default:
                break;
            }
        }
        //发票信息
        $data['taxinfo']=array(
            'tax_type'=>$datas['tax_type'],
            'tax_title'=>$sdf_order['tax_title'],
            'tax_content'=>$sdf_order['tax_content'],
        );
        //结算信息
        $data['total']=array(
            'cost_item'=>$sdf_order['cost_item'],
            'cost_freight'=>$sdf_order['shipping']['cost_shipping'],
            'cost_protect'=>$sdf_order['shipping']['cost_protect'],
            'discountPrice'=>$sdf_order['pmt_order'],
            'cost_payment'=>$sdf_order['payinfo']['cost_payment'],
            'cost_tax'=>$sdf_order['cost_tax'],
            'consumeScore'=>$sdf_order['score_u'],
            'totalGainScore'=>$sdf_order['score_g'],
            'total_amount'=>$sdf_order['total_amount'],
        );
        //return  $data['total'];
        $data['member_id']=$sdf_order['member_id'];
     
        //$data['order_objects']=$sdf_order['order_objects'];
       
        
       //获取商品信息
        $order_items=$this->app->model('order_items')->getList('goods_id,product_id,item_id,name,nums,price,item_type,score',array('order_id'=>$params['order_id']));
        foreach ($order_items as $key => $value) {
            $fmt_items[$value['product_id']]['product_id']=$value['product_id'];
            $fmt_items[$value['product_id']]['goods_id']=$value['goods_id'];
            $fmt_items[$value['product_id']]['name']=$value['name'];
            $fmt_items[$value['product_id']]['score']=$value['score'];
            $fmt_items[$value['product_id']]['nums']=$value['nums'];
            $fmt_items[$value['product_id']]['price']=$value['price'];
            $fmt_items[$value['product_id']]['item_id']=$value['item_id'];
            $fmt_items[$value['product_id']]['item_type']=$value['item_type'];
        }
        //return $order_items;

        foreach ($order_items as $key => $value) {
            $product_id[$key]=$value['product_id'];
        }
        //获取货品详情
        $product_items=$this->app->model('products')->getList('goods_id,product_id,spec_info,price,store',array('product_id|in'=>$product_id));
        //return $product_items;
        foreach ($product_items as $key => $value) {
            $fmt_product[$value['product_id']]['product_id']=$value['product_id'];
            $fmt_product[$value['product_id']]['goods_id']=$value['goods_id'];
            $fmt_product[$value['product_id']]['spec_info']=$value['spec_info'];
            //$fmt_product[$value['product_id']]['store']=$value['store'];
            $fmt_product[$value['product_id']]['goodsprice']=$value['price'];
        }
        //return  $fmt_product;
        foreach ($fmt_items as $key => $value) {
           $order_pmf[$value['product_id']]['product_id']=$value['product_id'];
           $order_pmf[$value['product_id']]['goods_id']=$value['goods_id'];
           $order_pmf[$value['product_id']]['name']=$value['name'];
           $order_pmf[$value['product_id']]['score']=$value['score'];
           $order_pmf[$value['product_id']]['nums']=$value['nums'];
           $order_pmf[$value['product_id']]['price']=$value['price'];
           $order_pmf[$value['product_id']]['item_type']=$value['item_type'];
           //$order_pmf[$value['product_id']]['store']=$fmt_product[$value['product_id']]['store'];
           $order_pmf[$value['product_id']]['goodsprice']=$fmt_product[$value['product_id']]['goodsprice'];
           $order_pmf[$value['product_id']]['spec_info']=$fmt_product[$value['product_id']]['spec_info'];
        }
        //return $order_pmf;
        foreach ($order_pmf as $key => $value) {
            if($order_pmf[$key]['item_type']=='product'){
                //$goods[$value['product_id']]=$value['product_id'];
                $goods['goods'][$value['product_id']]['product_id']=$value['product_id'];
                $goods['goods'][$value['product_id']]['goods_id']=$value['goods_id'];
                $goods['goods'][$value['product_id']]['goods_name']=$value['name'];
                $goods['goods'][$value['product_id']]['score']=$value['score'];
                $goods['goods'][$value['product_id']]['quantity']=$value['nums'];
                $goods['goods'][$value['product_id']]['item_type']=$value['item_type'];
               // $goods['goods'][$value['product_id']]['store']=$value['store'];
                $goods['goods'][$value['product_id']]['price']=$value['price'];
                //$goods['goods'][$value['product_id']]['goodsprice']=$value['goodsprice'];
                $goods['goods'][$value['product_id']]['spec_info']=$value['spec_info'];
                $goods['goods'][$value['product_id']]['discount_price']=$sdf_order['pmt_order'];
                $goods['goods'][$value['product_id']]['totle_price']=$value['nums']*$value['price']-$sdf_order['pmt_order'];

            }
            if($order_pmf[$key]['item_type']=='gift'){
                $gift['gift'][$value['product_id']]['product_id']=$value['product_id'];
                $gift['gift'][$value['product_id']]['gift_name']=$value['name'];
                $gift['gift'][$value['product_id']]['score']=$value['score'];
                $gift['gift'][$value['product_id']]['quantity']=$value['nums'];
                $gift['gift'][$value['product_id']]['price']=$value['goodsprice'];
                $gift['gift'][$value['product_id']]['spec_info']=$value['spec_info'];
            }
            
        }
         //组织优惠数据
        //return $order_pmf;
        foreach ($sdf_order['order_pmt'] as $key => $value) {
            $date['promotion'][$value['pmt_type']]['tag']=$value['pmt_tag'];
            $date['promotion'][$value['pmt_type']]['name']=$value['pmt_memo'];
        }

        //组织每个商品的赠品信息
        foreach ($goods['goods'] as $key => $value) {
            $goods['goods'][$value['product_id']]['promotion']=$date['promotion'];
            $goods['goods'][$value['product_id']]['gift']=$gift['gift'];
        }
        //return $goods;
        $this->pagedata['order'] = $data;
        

        // 支付方式的解析变化
        $obj_payments_cfgs = app::get('ectools')->model('payment_cfgs');
        $arr_payments_cfg = $obj_payments_cfgs->getPaymentInfo($sdf_order['payinfo']['pay_app_id']);
        
        $this->pagedata['order']['payinfo'] = array(
            'payid'=>$arr_payments_cfg['app_id'],
            'payname'=>$arr_payments_cfg['app_display_name'],

        );
        $this->pagedata['order']['goodsinfo']=$goods;
        return  $this->pagedata['order'];
    }
}

