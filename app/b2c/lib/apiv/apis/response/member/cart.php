<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c cart interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_member_cart
{
    public function __construct($app) {
        $this->objMath = kernel::single("ectools_math");
        $this->app = $app;
    }

    private function check_accesstoken($accesstoken,$member_id){
        $_GET['sess_id'] = $accesstoken;
        kernel::single("base_session")->start();
        $userObject = kernel::single('b2c_user_object');
        $id = $userObject->get_member_id();
        if( empty($id) || $member_id != $id ){
            return false;
        }
        return true;
    }

    /**
     * 根据会员ID获取购物车信息
     */
    public function get_cart_info($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        $mCart = app::get('b2c')->model('cart');
        $aCart = $mCart->get_objects($aData);
        $result['object']['goods'] = $this->_get_cart_goods_data($aCart['object']['goods'],$aCart['promotion']['goods']);
        $result['object']['gift'] = $this->_get_cart_gift_data($aCart['object']['gift']);
        if( empty($result['object']['goods']) && empty($result['object']['gift']) ){
            return array();
        }
        $cart_promotion_display = app::get('b2c')->getConf('cart.show_order_sales.type');
        if( $cart_promotion_display == 'true' ){//购物车是否显示订单促销
            $result['order_promotion'] = $this->_get_cart_promotion($aCart['promotion']);
        }
        $result['subtotal_goods_price'] = $aCart['subtotal_prefilter_after']; //商品总金额
        $result['subtotal_discount_amount'] = $aCart['discount_amount_order'];//订单优惠总金额
        $result['subtotal_gain_score'] = $aCart['subtotal_gain_score'];//订单可得总积分
        $result['subtotal_price'] = $this->objMath->number_minus(array($aCart['subtotal'], $aCart['subtotal_discount']));//总金额
        return $result;
    }

    /**
     * 订单促销
     */
    private function _get_cart_promotion($promotionCart){
        foreach( (array)$promotionCart['order'] as $i=>$row ){
            $return[$i]['name'] = $row['name'];
            $return[$i]['desc'] = $row['desc'];
            $return[$i]['tag'] = $row['desc_tag'];
        }
        return $return;
    }

    /**
     *  获取购物车商品数据结构
     */
    private function _get_cart_goods_data($goodsCart,$goodsPromotion){
        if( empty($goodsCart) ) return array();

        $storager = kernel::single('base_storager');
        foreach((array)$goodsCart as $i=>$goods ){
            $objectGoods[$i]['obj_ident'] = $goods['obj_ident'];
            $objectGoods[$i]['obj_type'] = $goods['obj_type'];

            $products = $goods['obj_items']['products'][0];
            $objectGoods[$i]['goods_id'] = $products['goods_id'];
            $objectGoods[$i]['product_id'] = $products['product_id'];
            $objectGoods[$i]['name'] = $products['name'];
            $objectGoods[$i]['spec_info'] = $products['spec_info'];
            $objectGoods[$i]['store_real'] = $goods['store']['real'];
            $objectGoods[$i]['quantity'] = $goods['quantity'];
            $objectGoods[$i]['price'] = $products['price']['price'];
            $discount_price = $goods['discount_amount_prefilter']+(($products['price']['price']-$products['price']['member_lv_price'])*$goods['quantity']);
            $objectGoods[$i]['discount_price'] = $discount_price;
            $objectGoods[$i]['total_price'] = $goods['subtotal_prefilter_after'];
            $objectGoods[$i]['score'] = $goods['subtotal_gain_score '] - $goods['subtotal_consume_score'];
            $objectGoods[$i]['pic'] = $products['thumbnail'] ? $products['thumbnail'] : $products['default_image']['thumbnail'];
            $objectGoods[$i]['pic'] = $storager->image_path($objectGoods[$i]['pic'],'m');

            //商品送赠品
            foreach( (array)$goods['gift'] as $k=>$gift ){
                $goods_promotion_gift[$k]['name'] = $gift['name'];
                $goods_promotion_gift[$k]['product_id'] = $gift['product_id'];
                $goods_promotion_gift[$k]['spec_info'] = $gift['spec_info'];
                $goods_promotion_gift[$k]['price'] = $gift['price']['price'] * $gift['quantity'];
                $goods_promotion_gift[$k]['quantity'] = $gift['quantity'];
            }
            $objectGoods[$i]['gift'] = $goods_promotion_gift;

            //商品促销
            if( $goodsPromotion[$goods['obj_ident']] ){
                foreach( (array)$goodsPromotion[$goods['obj_ident']] as $key=>$promotionRow ){
                    $promotionData[$key]['name'] = $promotionRow['name'];
                    $promotionData[$key]['tag'] = $promotionRow['desc_tag'];
                }
            }
            $objectGoods[$i]['promotion'] = $promotionData;
        }
        return $objectGoods;
    }

    /**
     *获取购物车赠品数据结构
     */
    private function _get_cart_gift_data($cartGiftData){
        if( empty($cartGiftData) ) return array();
        $storager = kernel::single('base_storager');
        //积分兑换赠品数据
        if( $cartGiftData['cart'] ){
            foreach( $cartGiftData['cart'] as $i=>$giftRow ){
                $giftData[$i]['obj_ident'] = $giftRow['obj_ident'];
                $giftData[$i]['obj_type'] = $giftRow['obj_type'];

                $giftData[$i]['goods_id'] = $giftRow['goods_id'];
                $giftData[$i]['product_id'] = $giftRow['product_id'];
                $giftData[$i]['name'] = $giftRow['name'];
                $giftData[$i]['spec_info'] = $giftRow['spec_info'];
                $giftData[$i]['store_real'] = $giftRow['params']['real'];
                $giftData[$i]['quantity'] = $giftRow['quantity'];
                $giftData[$i]['price'] = $giftRow['price']['price'];
                $giftData[$i]['consume_score'] = $giftRow['consume_score'];
                $giftData[$i]['pic'] = $giftRow['thumbnail'] ? $giftRow['thumbnail'] : $giftRow['default_image']['thumbnail'];
                if( !strpos($giftData[$i]['pic'] ,'://')){
                    $giftData[$i]['pic'] = $storager->image_path($objectGoods[$i]['pic'],'m');
                }
            }
        }
        $return['cart'] = $giftData;

        if( $cartGiftData['order'] ){
            foreach( $cartGiftData['order'] as $i=>$giftRow ){
                $orderGiftData[$i]['goods_id'] = $giftRow['goods_id'];
                $orderGiftData[$i]['product_id'] = $giftRow['product_id'];
                $orderGiftData[$i]['name'] = $giftRow['name'];
                $orderGiftData[$i]['spec_info'] = $giftRow['spec_info'];
                $orderGiftData[$i]['quantity'] = $giftRow['quantity'];
                $orderGiftData[$i]['price'] = $giftRow['price']['price'];
                $orderGiftData[$i]['pic'] = $giftRow['thumbnail'] ? $giftRow['thumbnail'] : $giftRow['default_image']['thumbnail'];
                if( !strpos($orderGiftData[$i]['pic'] ,'://')){
                    $orderGiftData[$i]['pic'] = $storager->image_path($objectGoods[$i]['pic'],'m');
                }
            }
        }
        $return['order'] = $orderGiftData;
        return $return;
    }

    /**
     * 保存会员新添加的购物车信息
     */
    public function add_cart($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        if( empty($params['num']) ){
            $params['num'] = 1;
        }else{
            $params['num'] = intval($params['num']);
        }

        if( empty($params['type']) || !in_array($params['type'],array('goods','gift')) ){
            $params['type'] = 'goods';
        }

        if( !intval($params['goods_id']) || !intval($params['product_id']) ){
            return $service->send_user_error('100001','参数错误');
        }

        // 过滤特殊字符
        $obj_filter = kernel::single('b2c_site_filter');
        $params = $obj_filter->check_input($params);
        $data['goods']['goods_id'] = intval($params['goods_id']);
        $data['goods']['product_id'] = intval($params['product_id']);
        $data['goods']['num'] = $params['num'];
        $data[0] = $params['type'];
        $type = $data[0];


        /**
         * 处理信息和验证过程
         * servicelist('b2c_cart_object_apps')=>
         * gift_cart_object_gift
         * b2c_cart_object_coupon
         * b2c_cart_object_goods
         */
        $arr_objects = array();
        if ($objs = kernel::servicelist('b2c_cart_object_apps'))
        {
            foreach ($objs as $obj)
            {
                if ($obj->need_validate_store()){
                    $arr_objects[$obj->get_type()] = $obj;
                }
            }
        }

        if (method_exists($arr_objects[$type], 'get_data'))
        {
            if (!$aData = $arr_objects[$type]->get_data($data,$msg))
            {
                $error['status'] = 'false';
                $error['message'] = $msg;
                return $error;
            }
        }
        // 进行各自的特殊校验
        if (method_exists($arr_objects[$type], 'check_object'))
        {
            if (!$arr_objects[$type]->check_object($aData,$msg))
            {
                $error['status'] = 'false';
                $error['message'] = $msg;
                return $error;
            }
        }
        $obj_cart_object = kernel::single('b2c_cart_objects');
        if (!$obj_cart_object->check_store($arr_objects[$type], $aData, $msg))
        {
            $error['status'] = 'false';
            $error['message'] = $msg;
            return $error;
        }

        $obj_ident = $obj_cart_object->add_object($arr_objects[$type], $aData, $msg);
        $return['status'] = $obj_ident ? 'true' : 'false';
        $return['message'] = $msg;
        return $return;
    }

    /**
     *  更新购物车信息购物车信息
     */
    public function update_cart($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }

        if( empty($params['type']) || !in_array($params['type'],array('goods','gift')) ){
            $params['type'] = 'goods';
        }
        if( empty($params['num']) ){
            $params['num'] = 1;
        }else{
            $params['num'] = intval($params['num']);
        }

        if( !intval($params['goods_id']) || !intval($params['product_id']) ){
            return $service->send_user_error('100001','参数错误');
        }

        // 过滤特殊字符
        $obj_filter = kernel::single('b2c_site_filter');
        $params = $obj_filter->check_input($params);
        $data['goods']['goods_id'] = intval($params['goods_id']);
        $data['goods']['product_id'] = intval($params['product_id']);
        $data['goods']['num'] = $params['num'];
        $data[0] = $params['type'];
        $type = $data[0];

        $obj_ident = $params['type'].'_'.$data['goods']['goods_id'].'_'.$data['goods']['product_id'];
        $arr_object['quantity'] = $params['num'];
        $mCartObject = app::get('b2c')->model('cart_objects');
        $_flag = $mCartObject->update_object($type,$obj_ident,$arr_object );

        $return['status'] = $obj_ident ? 'true' : 'false';
        $return['message'] = $msg;
        return $return;
    }

    /**
     *  清除购物车信息购物车信息
     */
    public function remove_cart($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }

        $mCartObject = app::get('b2c')->model('cart_objects');
        if( $params['remove_all'] == 'true'){
            $flag = $mCartObject->remove_object('', null, $msg);
            $return['status'] = $flag ? 'true' : 'false';
            $return['message'] = $msg;
            return $return;
        }else{
            if( empty($params['type']) || !in_array($params['type'],array('goods','gift')) ){
                $params['type'] = 'goods';
            }

            if( !intval($params['goods_id']) || !intval($params['product_id']) ){
                return $service->send_user_error('100002','参数错误');
            }

            // 过滤特殊字符
            $obj_filter = kernel::single('b2c_site_filter');
            $params = $obj_filter->check_input($params);
            $data['goods']['goods_id'] = $params['goods_id'];
            $data['goods']['product_id'] = $params['product_id'];

            $obj_ident = $params['type'].'_'.$data['goods']['goods_id'].'_'.$data['goods']['product_id'];
            $flag = $mCartObject->remove_object($params['type'], $obj_ident, $msg);
            $return['status'] = $flag ? 'true' : 'false';
            $return['message'] = $msg;
            return $return;
        }
    }

    /**
     * 根据地区ID，获取在次购物车中配送方式和配送价格
     */
    public function get_dlytype($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        $mCart = app::get('b2c')->model('cart');
        $aCart = $mCart->get_objects($aData);

        $all_dly_types = kernel::single('b2c_order_dlytype')->get_dlytype($this,$params['area_id'],$aCart);
        foreach ($all_dly_types as $i=>$rows)
        {
            if ($rows['is_threshold'] && $rows['threshold'])
            {
                $rows['threshold'] = unserialize(stripslashes($rows['threshold']));
                if (isset($rows['threshold']) && $rows['threshold'])
                {
                    foreach ($rows['threshold'] as $res)
                    {
                        if ($res['area'][1] > 0)
                        {
                            if ($cost_item >= $res['area'][0] && $cost_item < $res['area'][1])
                            {
                                $rows['firstprice'] = $res['first_price'];
                                $rows['continueprice'] = $res['continue_price'];
                            }
                        }
                        else
                        {
                            if ($cost_item >= $res['area'][0])
                            {
                                $rows['firstprice'] = $res['first_price'];
                                $rows['continueprice'] = $res['continue_price'];
                            }
                        }
                    }
                }
            }
            $delivery[$i]['dt_id'] = $rows['dt_id'];
            $delivery[$i]['has_cod'] = $rows['has_cod'];
            $delivery[$i]['dt_name'] = $rows['dt_name'];
            $delivery[$i]['detail'] = $rows['detail'];
            $delivery[$i]['protect'] = $rows['protect'];
            $delivery[$i]['protect_rate'] = $rows['protect_rate'];
            $delivery[$i]['minprice'] = $rows['minprice'];
            $delivery[$i]['money'] = utils::cal_fee($rows['dt_expressions'], $aCart['subtotal_weight'], $aCart['subtotal'], $rows['firstprice'], $rows['continueprice'], $rows['firstprice']);
        }
        return $delivery;
    }
}
