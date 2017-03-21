<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c user interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_response_member_user
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
     * 保存会员新建/编辑的收货地址
     */
    public function save_address($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }

        $must_params = array('ship_name','ship_area','ship_addr','ship_zip','is_default');

        foreach($must_params as $must_params_v){
            if(empty($params[$must_params_v])){
                $msg = $must_params_v.' 参数为必填参数';
                return $service->send_user_error($msg);
            }
        }

        if( empty($params['ship_tel']) && empty($params['ship_mobile']) ){
            return $service->send_user_error('电话号码和手机号码必填一项');
        }
        
        if( !empty($params['ship_id']) ){
            $data['addr_id'] = $params['ship_id'];
        }
        $data['name']       = $params['ship_name']  ;
        $data['area']       = $params['ship_area']  ;
        $data['addr']       = $params['ship_addr']  ;
        $data['zip']        = $params['ship_zip']   ;
        $data['tel']        = $params['ship_tel']   ;
        $data['mobile']     = $params['ship_mobile'];
        $data['def_addr'] = $params['is_default']   ;
        if( !isset($data['addr_id']) && $params['is_default'] == 'false' ){
            unset($data['def_addr']);
        }

        $save_data = kernel::single('b2c_member_addrs')->purchase_save_addr($data,$params['member_id'],$msg);
        $return['status'] = $save_data ? 'true' : 'false';
        $return['message'] = $msg;
        return $return;
    }

    /**
     * 根据会员查询收货地址
     */
    public function get_address($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        $obj_member_addrs = app::get('b2c')->model('member_addrs');
        $address_list = $obj_member_addrs->getList('*',array('member_id'=>$params['member_id']));
        foreach( (array)$address_list as $k=>$row ){
            $list[$k]['ship_id'] = $row['addr_id'];
            $list[$k]['ship_name'] = $row['name'];
            $list[$k]['ship_area'] = $row['area'];
            $list[$k]['ship_addr'] = $row['addr'];
            $list[$k]['ship_zip'] = $row['zip'];
            $list[$k]['ship_tel'] = $row['tel'];
            $list[$k]['ship_mobile'] = $row['mobile'];
            $list[$k]['is_default'] = $row['def_addr'] ? 'true' : 'false';
        }
        return $list;
    }

    /**
     * 用户基本信息查询
     */
    public function get_member_info($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        $memberData = kernel::single('b2c_user_object')->get_member_info();
        $return['member_id'] = intval($memberData['member_id']);
        $return['uname'] = $memberData['uname'];
        $return['point'] = $memberData['point'];
        $return['usage_point'] = $memberData['usage_point'];
        $return['email'] = $memberData['email'];
        $return['member_lv'] = $memberData['member_lv'];
        $return['levelname'] = $memberData['levelname'];
        $return['advance'] = $memberData['advance'];
        $return['sex'] = $memberData['sex'];
        return $return;
    }

    /**
     * 根据用户id调取其订单列表
     */
    public function get_order_list($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }

        $params['page_no'] = intval($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = intval($params['page_size']) ? $params['page_size'] : 10;

        $order = app::get('b2c')->model('orders');
        $aData = $order->fetchByMember($params['member_id'],$params['page_no'],$order_status=array(),$params['page_size']);
        $i = 0;
        foreach( (array)$aData['data'] as $row ){
            $orderData[$i]['order_id'] = $row['order_id'];
            $orderData[$i]['itemnum'] = $row['itemnum'];
            $orderData[$i]['amount'] = $row['cur_amount'];
            $orderData[$i]['createtime'] = $row['createtime'];
            $orderData[$i]['pay_status'] = $order->schema['columns']['pay_status']['type'][$row['pay_status']];//支付状态
            $orderData[$i]['ship_status'] = $order->schema['columns']['ship_status']['type'][$row['ship_status']];//发货状态
            $orderData[$i]['status'] = $order->schema['columns']['status']['type'][$row['status']];//订单状态
            $j=0;
            foreach( (array)$row['order_objects'] as $order_objects){
                foreach((array)$order_objects['order_items'] as $order_items){
                    $goodsItem[$j]['goods_id'] = intval($order_items['goods_id']);
                    $goodsItem[$j]['product_id'] = intval($order_items['products']['product_id']);
                    $goodsItem[$j]['goods_name'] = $order_items['name'];
                    $goodsItem[$j]['spec_info'] = $order_items['products']['spec_info'];
                    $goodsItem[$j]['quantity'] = intval($order_items['quantity']);
                    $goodsItem[$j]['item_type'] = $order_items['item_type'];
                    $gids[] = $order_items['goods_id'];
                    $j++;
                }
            }
            $imageData = $this->_get_gids_image(false,$gids);
            foreach( (array)$goodsItem as $key=>$row ){
                $goodsItem[$key]['goods_pic'] = $imageData[$row['goods_id']] ? $imageData[$row['goods_id']] : '';
            }
            $orderData[$i]['item'] = $goodsItem;
            $i++;
        }
        $return['orderData'] = $orderData;
        $return['pager_total'] = intval($aData['pager']['total']);
        return $return;
    }

    /**
     * 根据商品ID，获取到对应的默认图片
     * @params $imageIds array | string 图片ID
     * @params $gids array | string 商品ID
     */
    private function _get_gids_image($imageIds=false,$gids){
        if( !$imageIds ){
            $goodData = app::get('b2c')->model('goods')->getList('goods_id,image_default_id',array('goods_id'=>$gids));
            foreach( (array)$goodData as $goodsRow){
                $gid = $goodsRow['goods_id'];
                $imageIds[$gid] = $goodsRow['image_default_id'];
            }
        }

        $imageData = app::get('image')->model('image')->getList('url,image_id,s_url,m_url,l_url,last_modified',array('image_id'=>$imageIds));
        $resource_host_url = kernel::get_resource_host_url();
        foreach( (array)$imageData as $imageRow ){
            $image_id = $imageRow['image_id'];
            $imageUrl[$image_id]['s_url'] = $imageRow['s_url'] ? $imageRow['s_url'] : $imageRow['url'];
            if($imageUrl[$image_id]['s_url'] &&!strpos($imageUrl[$image_id]['s_url'],'://')){
                $imageUrl[$image_id]['s_url'] = $resource_host_url.'/'.$imageUrl[$image_id]['s_url'];
            }
            $imageUrl[$image_id]['m_url'] = $imageRow['m_url'] ? $imageRow['m_url'] : $imageRow['url'];
            if($imageUrl[$image_id]['m_url'] &&!strpos($imageUrl[$image_id]['m_url'],'://')){
                $imageUrl[$image_id]['m_url'] = $resource_host_url.'/'.$imageUrl[$image_id]['m_url'];
            }
            $imageUrl[$image_id]['l_url'] = $imageRow['l_url'] ? $imageRow['l_url'] : $imageRow['url'];
            if($imageUrl[$image_id]['l_url'] &&!strpos($imageUrl[$image_id]['l_url'],'://')){
                $imageUrl[$image_id]['l_url'] = $resource_host_url.'/'.$imageUrl[$image_id]['l_url'];
            }
        }

        foreach( (array)$imageIds as $gid=>$image_default_id ){
            $return[$gid] = $imageUrl[$image_default_id];
        }
        return $return;
    }

    /**
     * 根据用户id获取商品收藏列表
     */
    public function get_fav($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }

        $params['page_no'] = intval($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = intval($params['page_size']) ? $params['page_size'] : 10;

        $memberData = app::get('b2c')->model('members')->getRow('member_lv_id',array('member_id'=>$params['member_id']));
        $aData = kernel::single('b2c_member_fav')->get_favorite($params['member_id'],$memberData['member_lv_id'],$params['page_no'],$params['page_size']);
        $aProduct = $aData['data'];
        $i = 0;
        foreach($aProduct as $k=>$v){
            $favGoods[$i]['goods_id'] = intval($v['spec_desc_info'][0]['goods_id']);
            $favGoods[$i]['product_id'] = intval($v['spec_desc_info'][0]['product_id']);
            $favGoods[$i]['goods_name'] = $v['name'];
            $favGoods[$i]['spec_info'] = $v['spec_desc_info'][0]['spec_info'];
            $favGoods[$i]['goods_price'] = $v['spec_desc_info'][0]['price'];
            $favGoods[$i]['marketable'] = $v['marketable'];
            $imageIds[$v['spec_desc_info'][0]['goods_id']] = $v['image_default_id'];
            $i++;
        }
        $imageData = $this->_get_gids_image($imageIds,array());
        foreach( (array)$favGoods as $key=>$row ){
            $favGoods[$key]['goods_pic'] = $imageData[$row['goods_id']] ? $imageData[$row['goods_id']] : '';
        }

        $return['goods'] = $favGoods;
        $return['page'] = intval($aData['page']);
        return $return;
    }

}

