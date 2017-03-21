<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */

class giftpackage_ctl_site_giftpackage extends giftpackage_frontpage{

    /**
     * 礼包前台页面首页
     **/
    function index(){
        #$this->_response->set_header('Cache-Control', 'no-store');
        $_getParams = $this->_request->get_params();
        $id = $_getParams[0];
        if( !$id ) exit('参数错误！');

        $order_ref = $this->app->model('order_ref');
        $quantity = $order_ref->db->selectrow('SELECT count(quantity) as q from sdb_giftpackage_order_ref where giftpackage_id='.$id);

        $o = $this->app->model('giftpackage');
        $arr = $o->dump( $id );
        $this->path = array();
		#$this->path[] = array('title'=>app::get('b2c')->_('礼包'),'link'=>'#');
		$this->path[] = array('title'=>$arr['name'],'link'=>'#');
		$GLOBALS['runtime']['path'] = $this->path;
        if( !$arr ) {
            $this->begin(  );
            $this->end(false,'页面错误!');
        }
        
        //当前时间
        $time = time();
        if( ($arr['stime'] && $arr['stime']>$time) || ($arr['etime'] && $arr['etime']<=$time) ) {
            $this->begin(  );
            $this->end( false,'该礼包不在活动时间范围之内！' );
        }
        
        
        $imageDefault = app::get('image')->getConf('image.set');
        $image_default_id = $imageDefault['M']['default_image'];
        $this->pagedata['image_default_id'] = $image_default_id;
    
        $o = kernel::single("giftpackage_site_goods");
        $goods_info = array();
        if( is_array($arr['goods']) ) {
            foreach( $arr['goods'] as $val ) {
                $goods_info[] = $o->get_goods_info( explode(',',$val) );
            }
        } else {
            $goods_info[] = $o->get_goods_info( explode(',',$arr['goods']) );
        }
        #$this->pagedata['t'] = array('一','二','三','四','五','六','七','八');
        $this->pagedata['quantity'] = $quantity['q'];
        $this->pagedata['store'] = $arr['store'];
        $this->pagedata['count_goods'] = range(1,$arr['goods_count']);
        $this->pagedata['goods'] = $goods_info;
        
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        
		/** 暂时解决和b2c_ctl_product的问题，和这个控制器里面的这个变量冲突了, b2c/model/product.php的dump方法调用控制器了，不能直接调用，有待优化 **/
        $this->pagedata['request_url_1'] = $this->gen_url( array('app'=>'giftpackage','ctl'=>'site_giftpackage','act'=>'get_goods_spec') );
        $this->pagedata['package'] = $arr;
        
        
        $setting['buytarget'] = app::get('b2c')->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;
        
        
        $this->set_tmpl('giftpackage');
        $this->page('site/index/index.html');
    }
    
    public function get_goods_spec() {
        $gid = $this->_request->get_post('gid');
        if( !$gid ) exit('error!');
        $this->pagedata['goodshtml']['name'] = kernel::single("b2c_goods_detail_name")->show( $gid,$arrGoods );
        
        if( $arrGoods['spec'] && is_array($arrGoods['spec']) )  {
            foreach( $arrGoods['spec'] as $row ) {
                $option = $row['option'];
                if( $option && is_array($option) ) {
                    foreach( $option as $img ) {
                        foreach( (array)explode(',',$img['spec_goods_images']) as $imageid )
                            $return[$imageid] = base_storager::image_path($imageid,'s');
                    }
                }
            }
        }
        $arrGoods['spec2image'] = json_encode($return);

        $this->pagedata['goods'] = $arrGoods;
        

        $this->pagedata['goodshtml']['spec'] = kernel::single("b2c_goods_detail_spec")->show( $gid,$arrGoods );
        
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        $this->page( 'site/index/spec.html',true);
    }
    
    
    
    public function add_to_cart() {
        $arr = $this->get_data();
        if( !$arr['products'] && !$arr['id'] ) { //登录成功后跳转
            $status = false;
            $msg = '礼包还差几件商品，快去挑选吧！';
        } else {
            if(($return=kernel::single('giftpackage_cart_object_giftpackage')->add_object( array('giftpackage'=>$arr) ))===true) {
                unset($return);
                $status = true;
                $msg = app::get('giftpackage')->_('加入购物车成功！');
                if( $arr['checkout'] ) {
                    $url = array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'checkout');
                } else {
                    $url = array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index');
                }
            } else {
                $status = false;
                $msg = $return ? $return : app::get('giftpackage')->_('参数错误！');
                $url = array('app'=>'giftpackage', 'ctl'=>'site_giftpackage', 'act'=>'index','arg0'=>$arr['id']);
            }
        }
        if( !$status ) { //加入购物车失败
            if($_POST['mini_cart']){
                echo json_encode( array('error'=>$msg) );exit;
            }
        } else {
            if($_POST['mini_cart']){
                $arr = app::get('b2c')->model("cart")->get_objects();
                $temp = $arr['_cookie'];
                $this->pagedata['cartCount']      = $temp['CART_COUNT'];
                $this->pagedata['cartNumber']     = $temp['CART_NUMBER'];
                $this->pagedata['cartTotalPrice'] = $temp['CART_TOTAL_PRICE'];
                $this->page('site/cart/mini_cart.html', true,'b2c');return;
            }
        }
        $this->begin( $url );
        $this->end($status, $msg);
    }
    
    private function get_data() {
        $arr = $this->_request->get_params(true);
        $return['num'] = 1;
        $return['products'] = $arr['goods'];
        $return['id'] = $arr['id'];
        $return['checkout'] = $arr['checkout'];
        
        $this->o_b2c_products = app::get('b2c')->model('products');
        foreach( (array)$return['products'] as $key => $row ) {
            if( !$row['product_id'] || $row['product_id']=='null' ) {
                $arr_product_info = $this->o_b2c_products->getList( 'product_id',array('goods_id'=>$row['goods_id']) );
                if( !$arr_product_info || !is_array($arr_product_info) || count($arr_product_info)>1 ) return false;
                reset( $arr_product_info );
                $arr_product_info = current( $arr_product_info );
                $return['products'][$key]['product_id'] = $arr_product_info['product_id'];
            }
        }

        return $return;
    }
    
    public function remove_cart_to_disabled() {
        kernel::single('base_session')->start();
        $_obj_type  = $this->_request->get_param(0);
        $_obj_ident  = $this->_request->get_param(1);
        $_product_id = (int)$this->_request->get_param(2);
        $_SESSION['cart_objects_disabled_item'][$_obj_type][$_obj_ident][$_product_id] = 'true';
        $this->_response->set_http_response_code(404);return;
    }
    
}
