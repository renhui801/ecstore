<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * ctl_cart
 *
 * @uses b2c_frontpage
 * @package
 * @version $Id: ctl.cart.php 1952 2008-04-25 10:16:07Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author Wanglei <flaboy@zovatech.com>
 * @license Commercial
 */
class b2c_ctl_site_cart extends b2c_frontpage{

    var $customer_template_type='cart';
    var $noCache = true;
    var $show_gotocart_button = true;

    public function __construct(&$app) {

        parent::__construct($app);
        $shopname = app::get('site')->getConf('site.name');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('购物车').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('购物车_').'_'.$shopname;
            $this->description = app::get('b2c')->_('购物车_').'_'.$shopname;
        }
        $this->set_tmpl('cart');
        $this->mCart = $this->app->model('cart');
        $this->set_no_store();

        //kernel::single('base_session')->start();
        $this->obj_session = kernel::single('base_session');
        $this->obj_session->start();

        $this->member_status = $this->check_login();
        if(!$this->member_status){
            $this->pagedata['login'] = 'nologin';
        }

        $this->objMath = kernel::single("ectools_math");
        $this->pagedata['res_url'] = $this->app->res_url;
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
        $this->mCart->unset_data();
    }

    public function index(){
        $GLOBALS['runtime']['path'][] = array('link'=>$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index')),'title'=>'购物车');
        $this->_common(1);
        $this->_response->set_header('Cache-Control','no-store');
        $current_url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index'));
        $this->pagedata['go_back_link'] = ($_SERVER['HTTP_REFERER'] != $current_url) ? $_SERVER['HTTP_REFERER'] : 'javascript:window.history.go(-1);';
        setcookie('cart[go_back_link]', $this->pagedata['go_back_link'], 0, kernel::base_url() . '/');
        $this->pagedata['aCart']['subtotal_prefilter'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['discount_amount_prefilter']));
        $this->pagedata['aCart']['promotion_subtotal'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['subtotal_discount']));
        $this->pagedata['checkout_link'] = $this->gen_url( array('app'=>'b2c','act'=>'checkout','ctl'=>'site_cart') );
        $cart_json = kernel::single('b2c_cart_json');
        $currency = app::get('ectools')->model('currency');
        $Default_currency = $currency->getDefault();
        $this->pagedata['currency'] = $Default_currency['cur_sign'];
        $fororder_setting = app::get('b2c')->getConf('cart_fororder_setting');//是否开启凑单功能
        $this->pagedata['fororder_show'] = $fororder_setting['fororder']['show'];
        $cur = app::get('ectools')->model('currency');
        $this->pagedata['cart_promotion_display'] = $this->app->getConf('cart.show_order_sales.type');

        //货币格式输出
        $ret = $cur->getFormat();
        $ret =array(
            'decimals'=>$this->app->getConf('system.money.decimals'),
            'dec_point'=>$this->app->getConf('system.money.dec_point'),
            'thousands_sep'=>$this->app->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'=>$this->app->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'=>$this->app->getConf('system.money.decimals'),
            'sign' => $ret['sign']
        );
        $this->pagedata['money_format'] = json_encode($ret);
        $this->pagedata['json'] = $cart_json->get_json($this->pagedata);
        $this->page('site/cart/index.html');
    }

    // 添加
    public function add($type='goods') {
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

        $data = $this->_request->get_params(true);
        $post = $_POST;
        // 过滤特殊字符
        $obj_filter = kernel::single('b2c_site_filter');
        $data = $obj_filter->check_input($data);

        if($data['response_json'] == 'true'){//ajax提交返回错误
            $errorRequest = true;
        }
        //跳转到购物车
        $url = $this->gen_url(array('app'=>'b2c', 'ctl'=>'site_cart'));

        /**
         * 处理校验各自的数据是否可以加入购物车
         */
        if (!$arr_objects[$type])
        {
            $msg = app::get('b2c')->_('商品类型错误！');
            if($post['mini_cart']){
                $this->pagedata['errormsg'] = $msg;
                $this->page('site/cart/mini_cart_error.html', true);
                return;
            } else {
                $this->splash('error',$url,$msg,$errorRequest);
            }
        }
        if (method_exists($arr_objects[$type], 'get_data'))
        {
            if (!$aData = $arr_objects[$type]->get_data($data,$msg))
            {
                if($post['mini_cart']){
                    $this->pagedata['errormsg'] = $msg;
                    $this->page('site/cart/mini_cart_error.html', true);
                    return;
                } else {
                    $this->splash('error',$url,$msg,$errorRequest);
                }
            }
        }
        // 进行各自的特殊校验
        if (method_exists($arr_objects[$type], 'check_object'))
        {
            if (!$arr_objects[$type]->check_object($aData,$msg))
            {
                if($post['mini_cart']){
                    $this->pagedata['errormsg'] = $msg;
                    $this->page('site/cart/mini_cart_error.html', true);
                    return;
                } else {
                    $this->splash('error',$url,$msg,$errorRequest);
                }
            }
        }

        $arr = $this->app->model("cart")->get_objects();  //获取购物车数据
        if( $arr['items_count'] >= 150 )
        {
                $this->pagedata['errormsg'] = app::get('b2c')->_('对不起，您的购物车中的商品数量已达上限。');
                $this->page('site/cart/mini_cart_error.html', true);
                return;
        }
        $obj_cart_object = kernel::single('b2c_cart_objects');
        if (!$obj_cart_object->check_store($arr_objects[$type], $aData, $msg))
        {
            if($post['mini_cart']){
                $this->pagedata['errormsg'] = $msg;
                $this->page('site/cart/mini_cart_error.html', true);
                return;
            } else {
                $this->splash('error',$url,$msg,$errorRequest);
            }
        }
        /** end **/
        //快速购买
        if(isset($aData[1]) && $aData[1] == 'quick' && empty($this->member_status)){
            $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout')),null,$errorRequest);
        }

        if($aData['btype'] == 'is_fastbuy'){
            $obj_ident = $obj_cart_object->add_object($arr_objects[$type], $aData, $msg,true);
        }else{
            $obj_ident = $obj_cart_object->add_object($arr_objects[$type], $aData, $msg);
        }
        if(!$obj_ident){
            if($post['mini_cart']){
                $this->pagedata['errormsg'] = $msg;
                $this->page('site/cart/mini_cart_error.html', true);
                return;
            } else {
                $this->splash('error',$url,$msg,$errorRequest);
            }
        } else {
            if(isset($aData['btype']) && $aData['btype'] == 'is_fastbuy') {
                $this->_check_checkout('true',$errorRequest);
            } else {
                if($post['mini_cart']){
                    $arr = $this->app->model("cart")->get_objects();  //获取购物车数据
                    $temp = $arr['_cookie'];
                    $this->pagedata['cartCount']      = $temp['CART_COUNT'];
                    $this->pagedata['cartNumber']     = $temp['CART_NUMBER'];
                    $this->pagedata['cartTotalPrice'] = $temp['CART_TOTAL_PRICE'];
                    $this->page('site/cart/mini_cart.html', true);
                    return;
                }

                // coupon
                if($aData[0]=='coupon'){
                    if (!$data['response_json']){
                        $url = array('app'=>'b2c', ctl=>'site_cart','act'=>'checkout');
                        $this->redirect( $url );
                    }else{
                        $this->_common(1,$aData['is_fastbuy']);
                        //只输出能够使用的优惠劵信息
                        $return_coupon_data = array();
                        foreach($this->pagedata['aCart']['object']['coupon'] as $key=>$coupon_data){
                            if($coupon_data['used'] == 'true'){
                                $return_coupon_data[$key]['coupon'] = $coupon_data['coupon'];
                                $return_coupon_data[$key]['cpns_id'] = $coupon_data['cpns_id'];
                                $return_coupon_data[$key]['name'] = $coupon_data['name'];
                                $return_coupon_data[$key]['obj_ident'] = $coupon_data['obj_ident'];
                            }
                        }
                        if($return_coupon_data){
                            $arr_json_data = array(
                                'success'=>app::get('b2c')->_('优惠券使用成功！'),
                                'data'=>$return_coupon_data,
                                'md5_cart_info'=>kernel::single('b2c_cart_objects')->md5_cart_objects(),
                            );
                            echo json_encode($arr_json_data);exit;
                        }else{
                            $ident = 'coupon_'.$aData['coupon'];
                            $this->app->model('cart_objects')->remove_object('coupon', $ident, $msg);
                            $url = array('app'=>'b2c', 'ctl'=>'site_cart','act'=>'checkout');
                            $this->splash('error',$url,'优惠券使用失败！',true);
                        }

                    }
                }else{
                    $url = $this->gen_url(array('app'=>'b2c', ctl=>'site_cart'));
                    $this->splash('success',$url,null,$errorRequest);
                }
            }
        }
    }


    // 修改 - 完全数据
    public function update() {
        $msg = "";
        $this->update_cart($msg);
        /** 完全数据 **/
        $this->_cart_main($msg);
    }


    /** 最小数据的修改对应的控制器 **/
    public function updateMiniCart() {
        $this->update_cart($msg,'mini');
        /** 最小数据 **/
        $this->_cart_main($msg,'mini');
    }

    /** 中等数据购物车修改控制器方法 **/
    public function update_middle() {
        $this->update_cart($msg,'middle');
        /** 中等数据 **/
        $this->_cart_main($msg,'middle');
    }

    /**
     * 修改购物车的方法-私有，只处理数据
     * @param string error message
     * @param string json type
     * @return null
     */
    private function update_cart(&$msg='',$type='all') {
        $aParams = $this->_request->get_params(true);
        $mCartObject = $this->app->model('cart_objects');
        $aCart = $this->mCart->get_basic_objects();
        foreach($aCart as $row)
        {
             if( isset($aParams['modify_quantity'][$row['obj_ident']]) )
             {
                $update_row = $row;
                break;
             }
        }
        if($aParams['modify_quantity'] && is_array($aParams['modify_quantity'])){
            $view = "";
            switch ($type){
                case 'mini':
                    $view = "site/cart/mini_index.html";
                break;
                case 'middle':
                    $view = "site/cart/middle_index.html";
                break;
                case 'all':
                default:
                    $view = "site/cart/index.html";
                break;
            }

            foreach ($aParams['modify_quantity'] as $obj_ident=>$arr_object){
             $temp = $aParams['modify_quantity'][$obj_ident];
             $flag = $this->_v_cart_object($temp, $update_row, false);
                $arr_object['quantity'] = (float)$arr_object['quantity'];
                $_flag = $mCartObject->update_object( $aParams['obj_type'],$obj_ident,$arr_object );

                if( is_array($_flag) && isset($_flag['status']) && isset($_flag['msg']) ) {
                    if( $_flag['status'] ) {
                        $this->ajax_update = true;
                        $this->update_obj_ident['ident'] = $obj_ident; //值不同。修改 失败直接推出循环
                        $msg = $_flag['msg'];
                    } else {
                        $error_json = array(
                            'error'=>$_flag['msg'],
                        );
                        $this->pagedata = $error_json;
                        $this->page($view);
                    }
                } else {
                    if( !$_flag ) {
                        $error_json = array(
                            'error'=>app::get('b2c')->_('购物车修改失败！'),
                        );
                        $this->pagedata = $error_json;
                        $this->page($view);
                    } else {
                        $this->ajax_update = true;
                        $this->update_obj_ident['ident'] = $obj_ident; //值不同。修改 失败直接推出循环
                        $msg = app::get('b2c')->_('购物车修改成功！');
                    }
                }
            }
        }
    }


    // 删除&清空
    public function remove() {
        $msg = '';
        $this->remove_cart_object($msg);

        $this->_cart_main($msg);
    }

    // 删除优惠券 优惠券移动到checkout
    public function removeCartCoupon() {
        $msg = '';
        $this->remove_cart_object($msg,'coupon');

        $this->_cart_main($msg,'coupon');
    }

    // 删除&清空 迷你购物车删除商品接口
    public function removeMiniCart() {
        $msg = '';
        $this->remove_cart_object($msg,'mini');

        $this->_cart_main($msg,'mini');
    }

    // 返回中等数据的删除机制
    public function remove_middle() {
        $msg = '';
        $this->remove_cart_object($msg,'middle');

        $this->_cart_main($msg,'middle');
    }

    /**
     * 删除购物车的方法
     * @param string message
     * @param string json type
     * @return null
     */
    private function remove_cart_object(&$msg='',$type='all'){
        $aParams = $this->_request->get_params(true);
        $mCartObject = $this->app->model('cart_objects');
        $this->ajax_html = true;  //用于返回页面识别。当无商品是跳转至cart_empty

        $view = "";
        switch ($type){
        case 'mini':
            if($aParams[2]){
                $aParams['modify_quantity'][$aParams[1]]['adjunct'][][$aParams[2]] = array('quantity'=>1);
            }else{
                $aParams['modify_quantity'][$aParams[1]] = array('quantity'=>1);
            }
            $aParams['obj_type'] = $aParams[0];
            $view = "site/cart/mini/view.html";
            break;
        case 'middle':
            $view = "site/cart/middle_index.html";
            break;
        case 'all':
        default:
            $view = "site/cart/index.html";
            break;
        }
        if ($aParams[0] == 'coupon'){
            $ident = $aParams['cpn_ident'];

            $flag = $mCartObject->remove_object('coupon', $ident, $msg);
            if( !$flag ){
                $error_json = array(
                    'error'=>$msg,
                );
                echo json_encode($error_json);
            }
        }else{
            // 清空购物车
            if($aParams[0] == 'all' || empty($aParams['modify_quantity'])) {
                $obj_type = null;
                if (!$mCartObject->remove_object('', null, $msg)){
                    // 不带入参清空所有的
                    $error_json = array(
                        'error'=>$msg,
                    );
                    echo json_encode($error_json);
                }
            } else {
                // 删除单一商品.
                if($aParams['modify_quantity'] && is_array($aParams['modify_quantity'])){
                    foreach ($aParams['modify_quantity'] as $obj_ident=>$arr_object){
                        if ($arr_object['quantity']){
                            // 删除整个商品对象.
                            if (!$mCartObject->remove_object($aParams['obj_type'], $obj_ident, $msg)){
                                $error_json = array(
                                    'error'=>$msg,
                                );
                                if($type == 'mini'){
                                    $this->pagedata = $error_json;
                                    $this->page($view);
                                }else{
                                    echo json_encode($error_json);
                                }
                            }
                        }else{
                            // 删除购物车对象中的附属品，配件和赠品等.
                            foreach ($arr_object as $obj_type=>$arr_quantity){
                                if (!$mCartObject->remove_object_part($obj_type, $obj_ident, $arr_quantity, $msg)){
                                    $error_json = array(
                                        'error'=>$msg,
                                    );
                                    if($type == 'mini'){
                                        $this->pagedata = $error_json;
                                        $this->page($view);
                                    }else{
                                        echo json_encode($error_json);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function remove_cart_to_disabled() {
        $_obj_type  = $this->_request->get_param(0);
        $_obj_ident  = $this->_request->get_param(1);
        $_product_id = (int)$this->_request->get_param(2);
        $_SESSION['cart_objects_disabled_item'][$_obj_type][$_obj_ident]['gift'][$_product_id] = 'true';
        kernel::single('site_router')->http_status(404);return;
    }



    public function _check_checkout($isfastbuy=0,$request=false){
        if(!$request){
            $request = $_POST['response_json'];
        }
        if (!$this->member_status){
          if($isfastbuy){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginBuy','arg0'=>'true'));
            $this->splash('success',$url,null,$request);
          }else{
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginBuy'));
            $this->splash('success',$url,null,$request);
          }
        }
        // 初始化购物车数据
        $this->_common(false,$isfastbuy);
        $this->begin(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index'));

        // 购物车是否为空
        if ($this->pagedata['is_empty'])
        {
            $this->end(false, app::get('b2c')->_('购物车为空！'),null,null,true);
        }

        // 购物是否满足起订量和起订金额
        if ((isset($this->pagedata['aCart']['cart_status']) && $this->pagedata['aCart']['cart_status'] == 'false') && (isset($this->pagedata['aCart']['cart_error_html']) && $this->pagedata['aCart']['cart_error_html'] != ""))
        {
            $this->end(false, $this->pagedata['aCart']['cart_error_html']);
        }

        $this->splash('success',$this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout','arg0'=>$isfastbuy)),null,$request);
    }

    /**
     * checkout
     * 切记和admin/order:create保持功能上的同步
     *
     * @access public
     * @return void
     */
    public function checkout($isfastbuy=0)
    {
        /**
         * 取到扩展参数
         */
        $arr_args = func_get_args();
        $arr_args = array(
            'get' => $arr_args,
            'post' => array('modify_quantity'=>$_POST['modify_quantity']),
        );
        $this->pagedata['json_args'] = json_encode($arr_args);
        if($isfastbuy){
            $this->pagedata['is_fastbuy'] = $isfastbuy;
        }

        if (!$this->member_status){
          if($isfastbuy){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginBuy','arg0'=>'true'));
            $this->splash('success',$url,null,$_POST['response_json']);
          }else{
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginBuy'));
            $this->splash('success',$url,null,$_POST['response_json']);
          }
        }
        // 初始化购物车数据
        $this->_common(false,$isfastbuy);
        $this->begin(array('app'=>'b2c','ctl'=>'site_cart','act'=>'index'));

        // 购物车是否为空
        if ($this->pagedata['is_empty'])
        {
          $url = $this->gen_url(array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index'));
          $this->_response->set_redirect($url)->send_headers();
        }

        // 购物是否满足起订量和起订金额
        if ((isset($this->pagedata['aCart']['cart_status']) && $this->pagedata['aCart']['cart_status'] == 'false') && (isset($this->pagedata['aCart']['cart_error_html']) && $this->pagedata['aCart']['cart_error_html'] != ""))
        {
            $this->end(false, $this->pagedata['aCart']['cart_error_html']);
        }

        $this->checkout_result($isfastbuy);
    }

    /**
     * checkout 结果页面
     * @params int
     * @return null
     */
    public function checkout_result($isfastbuy=0){
        $this->pagedata['checkout'] = 1;
        $this->pagedata['md5_cart_info'] = kernel::single("b2c_cart_objects")->md5_cart_objects($isfastbuy);
        //会员信息
        $arrMember = kernel::single('b2c_user_object')->get_members_data(array('members'=>'member_id,cur',));
        $arrMember = $arrMember['members'];

        $this->pagedata['member_id'] = $arrMember['member_id'];
        /** 判断请求的参数是否是group **/
        $arr_request_params = $this->_request->get_params();
        //是否是团购订单
        $arr_request_params[0] == 'group' ? $this->pagedata['is_group_orders'] = 'true' : $this->pagedata['is_group_orders'] = 'false';
        $this->pagedata['app_id'] = $app_id;

        // 如果会员已登录，查询会员的信息
        $obj_member_addrs = $this->app->model('member_addrs');
        $obj_dltype = $this->app->model('dlytype'); //配送方式model
        $member_point = 0; //会员积分
        $str_def_currency = $arrMember['cur'] ? $arrMember['cur'] : ""; //会员设置的默认货币

        /*获取收货地址 start*/
        $def_addr = kernel::single('b2c_member_addrs')->get_default_addr($arrMember['member_id']);
        $this->pagedata['def_addr'] = $def_addr;
        $member_addr_list = $obj_member_addrs->getList('*',array('member_id'=>$arrMember['member_id']));
        $this->pagedata['member_addr_list'] = $member_addr_list;
        //邮编是否开启
        $this->pagedata['site_checkout_zipcode_required_open'] = $this->app->getConf('site.checkout.zipcode.required.open');
        /* 是否开启配送时间的限制 */
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        /*收货地址 end*/

        if($def_addr){
            //是否有默认的当前的配送方式
            $area = explode(':',$def_addr['area']);
            $this->pagedata['dlytype_html'] = kernel::single('b2c_order_dlytype')->select_delivery_method($this,$area[2],$this->pagedata['aCart']);
            $this->pagedata['shipping_method'] = (isset($_COOKIE['purchase']['shipping']) && $_COOKIE['purchase']['shipping']) ? unserialize($_COOKIE['purchase']['shipping']) : '';
            $this->pagedata['has_cod'] = (isset($this->pagedata['shipping_method']['has_cod']) && $this->pagedata['shipping_method']['has_cod']) ? $this->pagedata['shipping_method']['has_cod'] : 'false';
        }

        $currency = app::get('ectools')->model('currency');
        if($this->pagedata['shipping_method']){
            // 是否有默认的支付方式
            $this->pagedata['arr_def_payment'] = (isset($_COOKIE['purchase']['payment']) && $_COOKIE['purchase']['payment']) ? unserialize($_COOKIE['purchase']['payment']) : '';
            /*支付方式列表*/
            $currency = app::get('ectools')->model('currency');
            $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');
            if (!$str_def_currency){
                $arrDefCurrency = $currency->getDefault();
                $str_def_currency = $arrDefCurrency['cur_code'];
            }else{
                $arrDefCurrency = $currency->getcur($str_def_currency);
            }
            $aCur = $currency->getcur($str_def_currency);
            $this->pagedata['current_currency'] = $str_def_currency;
            $obj_payments = kernel::single('ectools_payment_select');
            $this->pagedata['payment_html'] = $obj_payments->select_pay_method($this, $arrDefCurrency);
            /*end*/

            $ret = $currency->getFormat();
            $ret =array(
                'decimals'=>$this->app->getConf('system.money.decimals'),
                'dec_point'=>$this->app->getConf('system.money.dec_point'),
                'thousands_sep'=>$this->app->getConf('system.money.thousands_sep'),
                'fonttend_decimal_type'=>$this->app->getConf('system.money.operation.carryset'),
                'fonttend_decimal_remain'=>$this->app->getConf('system.money.decimals'),
                'sign' => $ret['sign']
            );
            $this->pagedata['money_format'] = json_encode($ret);
        }

        //是否开启发票
        $trigger_tax = $this->app->getConf('site.trigger_tax');
        if($trigger_tax == 'true'){
            $personal_tax_ratio = $this->app->getConf('site.personal_tax_ratio'); //个人发票税率
            $company_tax_ratio = $this->app->getConf('site.company_tax_ratio'); //公司发票税率
            $tax_content = $this->app->getConf('site.tax_content'); //发票内容选项
            if($tax_content){
                $arr_tax_content = explode(',',$tax_content);
                foreach($arr_tax_content as $tax_content_value){
                    $select_tax_content[$tax_content_value] = $tax_content_value;
                }
            }
            $tax_setting = array(
                'trigger_tax' => $trigger_tax,
                'personal_tax_ratio' => $personal_tax_ratio,
                'company_tax_ratio' => $company_tax_ratio,
                'tax_content' =>$select_tax_content ? $select_tax_content : 0,
            );
            $this->pagedata['tax_setting'] = $tax_setting;
        }//end 发票

        /**
         * 取到优惠券的信息
         */
        $oCoupon = kernel::single('b2c_coupon_mem');
        $aData = $oCoupon->get_list_m($arrMember['member_id']);
        if( is_array($aData) ) {
            foreach( $aData as $_key => $_val ) {
                if( $_val['memc_used_times'] ) unset($aData[$_key]);
            }
        }
        $this->pagedata['coupon_lists'] = $aData;
        /*end*/


        $total_item = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount_prefilter']));
        // 取到商店积分规则
        $policy_method = $this->app->getConf("site.get_policy.method");
        switch ($policy_method)
        {
            case '1':
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
            case '2':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $policy_rate = $this->app->getConf('site.get_rate.method');
                $subtotal_gain_score = round($this->objMath->number_plus(array(0, $this->pagedata['aCart']['subtotal_gain_score'])));
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            case '3':
                $subtotal_consume_score = round($this->pagedata['aCart']['subtotal_consume_score']);
                $subtotal_gain_score = round($this->pagedata['aCart']['subtotal_gain_score']);
                $totalScore = round($this->objMath->number_minus(array($subtotal_gain_score, $subtotal_consume_score)));
                break;
            default:
                $subtotal_consume_score = 0;
                $subtotal_gain_score = 0;
                $totalScore = 0;
                break;
        }

        $total_amount = $this->objMath->number_minus(array($this->pagedata['aCart']["subtotal"], $this->pagedata['aCart']['discount_amount']));
        if ($total_amount < 0) $total_amount = 0;

        // 是否可以用积分抵扣
        $obj_point_dis = kernel::service('b2c_cart_point_discount');
        if ($obj_point_dis){
            $obj_point_dis->set_order_total($total_amount);
            $this->pagedata['point_dis_html'] = $obj_point_dis->get_html($arrMember['member_id']);
            $this->pagedata['point_dis_js'] = $obj_point_dis->get_javascript($arrMember['member_id']);
        }

        // 得到cart total支付的信息
        $this->pagedata['order_detail'] = array(
            'cost_item' => $total_item,
            'total_amount' => $total_amount,
            'currency' => $this->app->getConf('site.currency.defalt_currency'),
            'pmt_order' => $this->pagedata['aCart']['discount_amount_order'],
            'pmt_amount' => $this->pagedata['aCart']['discount_amount'],
            'totalConsumeScore' => $subtotal_consume_score,
            'totalGainScore' => $subtotal_gain_score,
            'totalScore' => $totalScore,
            'cur_code' => $strDefCurrency,
            'cur_display' => $strDefCurrency,
            'cur_rate' => $aCur['cur_rate'],
            'final_amount' => $currency->changer($total_amount, $this->app->getConf("site.currency.defalt_currency"), true),
        );

        //会员积分
       $this->pagedata['order_detail']['totalScore'] = $member_point;
        $odr_decimals = $this->app->getConf('system.money.decimals');
        $total_amount = $this->objMath->get($this->pagedata['order_detail']['total_amount'], $odr_decimals);
        $this->pagedata['order_detail']['discount'] = $this->objMath->number_minus(array($this->pagedata['order_detail']['total_amount'], $total_amount));
        $this->pagedata['order_detail']['total_amount'] = $total_amount;
        $this->pagedata['order_detail']['current_currency'] = $strDefCurrency;

        // 获得商品的赠品信息
        $arrM_info = array();
        foreach ($this->pagedata['aCart']['object']['goods'] as $arrGoodsInfo){
            if (isset($arrGoodsInfo['gifts']) && $arrGoodsInfo['gifts']){
                $this->pagedata['order_detail']['gift_p'][] = array(
                    'storage' => $arrGoodsInfo['gifts']['storage'],
                    'name' => $arrGoodsInfo['gifts']['name'],
                    'nums' => $arrGoodsInfo['gifts']['nums'],
                );
            }

            // 得到商品购物信息的必填项目
            $goods_id = $arrGoodsInfo['obj_items']['products'][0]['goods_id'];
            $product_id = $arrGoodsInfo['obj_items']['products'][0]['product_id'];
            // 得到商品goods表的信息
            $objGoods = $this->app->model('goods');
            $arrGoods = $objGoods->dump($goods_id, 'type_id');
            if (isset($arrGoods) && $arrGoods && $arrGoods['type']['type_id']){
                $objGoods_type = $this->app->model('goods_type');
                $arrGoods_type = $objGoods_type->dump($arrGoods['type']['type_id'], '*');

                if ($_COOKIE['checkout_b2c_goods_buy_info']){
                    $goods_need_info = json_decode($_COOKIE['checkout_b2c_goods_buy_info'], 1);

                }
                if ($arrGoods_type['minfo']){
                    if ($arrGoodsInfo['obj_items']['products'][0]['spec_info']){
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'] . '(' . $arrGoodsInfo['obj_items']['products'][0]['spec_info'] . ')';
                    }else{
                        $arrM_info[$product_id]['name'] = $arrGoodsInfo['obj_items']['products'][0]['name'];
                    }
                    $arrM_info[$product_id]['nums'] = $this->objMath->number_multiple(array($arrGoodsInfo['obj_items']['products'][0]['quantity'],$arrGoodsInfo['quantity']));

                    foreach ($arrGoods_type['minfo'] as $key=>$arr_minfo){
                        if (isset($goods_need_info[$product_id][$key]) && $arr_minfo['label'] == $goods_need_info[$product_id][$key]['name']){
                            $arr_minfo['value'] = $goods_need_info[$product_id][$key]['val'][0];
                        }else{
                            $no_value = true;
                        }
                        $arrM_info[$product_id]['minfo'][] = $arr_minfo;
                    }
                }
            }
        }

        if($no_value){
            $this->pagedata['has_goods_minfo'] = false;
        }else{
            $this->pagedata['has_goods_minfo'] = true;
        }
        $this->pagedata['minfo'] = $arrM_info;
        $this->pagedata['base_url'] = kernel::base_url().'/';
        // checkout result 页面添加项目埋点
        foreach( kernel::servicelist('b2c.checkout_add_item') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addItem') ) {
                    $services->addItem($this);
                }
            }
        }
        $this->page('site/cart/checkout/index.html',false,$app_id);
    }

    //送货地址编辑
    function shipping_edit(){
        $this->set_header();
        //邮编是否开启
        $this->pagedata['site_checkout_zipcode_required_open'] = $this->app->getConf('site.checkout.zipcode.required.open');
        $member_id = kernel::single('b2c_user_object')->get_member_id();
        if( $_POST['address'] && $member_id){
            $address = json_decode($_POST['address'],true);
            $addr = app::get('b2c')->model('member_addrs')->getList('*',array('addr_id'=>$address['addr_id'],'member_id'=>$member_id));
            $this->pagedata['edit_addr'] = $addr[0];
        }
        $this->pagedata['is_shipping_edit'] = true;
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        echo  $this->fetch('site/cart/checkout/shipping_edit.html');
        exit;
    }

    //保存送货地址
    function shipping_save(){
        $this->set_header();
        $member_id = kernel::single('b2c_user_object')->get_member_id();
        if($_POST['address']){
            $address = json_decode($_POST['address'],true);
            unset($_POST['address']);
            $_POST['addr_id'] = $address['addr_id'];
        }
        if(!$_POST['addr_id']){
            $this->pagedata['shipping_add'] = 'true';
        }
        if($this->app->getConf('site.checkout.zipcode.required.open') == 'true' && empty($_POST['zip']) ) {
            $this->splash('error',null,app::get('b2c')->_('邮编为必填项'),true);
        }
        $save_data = kernel::single('b2c_member_addrs')->purchase_save_addr($_POST,$member_id,$msg);
        if($save_data){
            $this->pagedata['save_addr'] = $save_data;
        }else{
            $this->splash('error',null,$msg,true);
        }
        /* 是否开启配送时间的限制 */
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        echo $this->fetch('site/cart/checkout/shipping_save.html');
    }

    //删除送货地址
    function shipping_delete(){
        $member_id = kernel::single('b2c_user_object')->get_member_id();
        if($member_id  && $_POST['address'] ){
            $address = json_decode($_POST['address'],true);
            $flag =  app::get('b2c')->model('member_addrs')->delete(array('addr_id'=>$address['addr_id'],'member_id'=>$member_id));
            if( $flag )
                echo  json_encode(array('success'=>'删除成功'));
            else
                echo  json_encode(array('error'=>'删除失败'));
        }else{
            echo json_encode(array('error'=>'会员未登录或参数错误'));
        }
        exit;
    }

    //确认送货地址
    function shipping_confirm(){
        $this->set_header();
        $member_id = kernel::single('b2c_user_object')->get_member_id();
        $obj_addr = kernel::single('b2c_member_addrs');
        //新增地址
        if(!$_POST['address']){
            $save_data = $obj_addr->purchase_save_addr($_POST,$member_id,$msg);
            $addr_id = $save_data['addr_id'];
            $this->pagedata['def_addr'] = $save_data;
        }else{
            $address = json_decode($_POST['address'],true);
            if($address['addr_id']){
                $def_addr = app::get('b2c')->model('member_addrs')->getList('*',array('addr_id'=>$address['addr_id']));
                if($this->app->getConf('site.checkout.zipcode.required.open') == 'true' && empty($def_addr[0]['zip']) ) {
                    $this->splash('error',null,app::get('b2c')->_('邮编为必填项'),true);
                }
                if($def_addr){
                    $this->pagedata['def_addr'] = $def_addr['0'];
                }else{
                    $this->splash('error',null,app::get('b2c')->_('收货地址ID错误'),true);
                }
            }else{
                $this->splash('error',null,app::get('b2c')->_('收货地址ID错误'),true);
            }
            $addr_id = $address['addr_id'];
        }
        $this->pagedata['site_checkout_receivermore_open'] = $this->app->getConf('site.checkout.receivermore.open');
        $seKey = md5($this->obj_session->sess_id().$member_id);
        setcookie('purchase[addr][usable]', $seKey, 0, kernel::base_url() . '/');
        setcookie('purchase[addr][addr_id]', $addr_id, 0, kernel::base_url() . '/');
        setcookie("purchase[shipping]", "", time() - 3600, kernel::base_url().'/');
        setcookie("purchase[payment]", "", time() - 3600, kernel::base_url().'/');
        $this->app->model('members')->update(array('addon'=>$this->pagedata['def_addr']), array('member_id'=>$member_id));
        echo $this->fetch('site/cart/checkout/shipping_confirm.html');
    }

    //配送方式根据送货地址联动
    public function delivery_change(){
        $this->set_header();
        $this->_common(false,$_POST['isfastbuy']);
        $area_id = $_POST['area'];
        $shipping_method = $_POST['shipping_method'];
        $obj_delivery = new b2c_order_dlytype();
        $sdf = array();
        $sdf = $this->pagedata['aCart'];
        if (isset($_POST['payment']) && $_POST['payment'])
            $sdf['pay_app_id'] = $_POST['payment'];
        $this->pagedata['app_id'] = $app_id;
        echo $obj_delivery->select_delivery_method($this,$area_id,$sdf,$shipping_method);exit;
    }

    //确认配送方式
    public function delivery_confirm(){
        $this->set_header();
        if (!isset($_POST['shipping']) || !$_POST['shipping']){
            $this->splash('error',null,app::get('b2c')->_('配送方式的id不能为空'),true);
        }

        $shipping = json_decode($_POST['shipping'],true);
        $arr_shipping = array(
            'shipping_id'=>$shipping['id'],
            'shipping_name'=>$shipping['dt_name'],
            'money'=>$shipping['money'],
            'has_cod'=>$shipping['has_cod'],
            'is_protect'=>$_POST['is_protect'],
        );
        setcookie('purchase[shipping]', serialize($arr_shipping), 0, kernel::base_url() . '/');
        setcookie("purchase[payment]", "", time() - 3600, kernel::base_url().'/');
        $this->pagedata['shipping_method'] = $arr_shipping;
        echo $this->fetch('site/cart/checkout/delivery_confirm.html');
    }

    //支付方式根据配送方式联动
    public function payment_change(){
        $this->set_header();
        $obj_payment_select = new ectools_payment_select();
        if($_POST['payment']['currency']){
            $sdf['cur'] = $_POST['payment']['currency'];
        }

        if($_POST['shipping']){
            $shipping = json_decode($_POST['shipping'],true);
            $this->pagedata['has_cod'] = $shipping['has_cod'];
        }
        $currency = app::get('ectools')->model('currency');
        $this->pagedata['currencys'] = $currency->getList('cur_id,cur_code,cur_name');
        $def_currency = app::get('b2c')->getConf('site.currency.defalt_currency');
        $this->pagedata['current_currency'] = $sdf['cur'] ? $sdf['cur'] : $def_currency;
        $this->pagedata['app_id'] = $app_id;
        echo $obj_payment_select->select_pay_method($this, $sdf, false);exit;
    }

    // 确认支付方式
    public function payment_confirm(){
        $this->set_header();
        if (!isset($_POST['payment']['pay_app_id']) || !$_POST['payment']['pay_app_id']){
            $msg =  app::get('b2c')->_('请选择一种支付方式');
            echo json_encode(array('error'=>$msg));exit;
        }

        $payment_info = json_decode($_POST['payment']['pay_app_id'],true);
        $arr_payment = array(
            'pay_app_id'=>$payment_info['pay_app_id'],
            'app_display_name'=>$payment_info['payment_name'],
        );
        setcookie('purchase[payment]', serialize($arr_payment), 0, kernel::base_url() . '/');
        $this->pagedata['arr_def_payment'] = $arr_payment;
        echo $this->fetch('site/cart/checkout/payment_confirm.html');
    }


    public function total(){
        $this->_common(false,$_POST['isfastbuy']);
        $obj_total = new b2c_order_total();
        $sdf_order = $_POST;
        if($_POST){
            $payment = json_decode($_POST['payment']['pay_app_id'],true);
            $shipping = json_decode($_POST['shipping'],true);
            $address = json_decode($_POST['address'],true);
            $address_area = explode(':',$address['area']);
        }
        $sdf_order['cur'] = $_POST['payment']['currency'];
        $sdf_order['shipping_id'] = $shipping['id'];
        $sdf_order['is_protect'] = $_POST['is_protect'];
        $sdf_order['is_tax'] = $_POST['payment']['is_tax'];
        $sdf_order['tax_type'] = $_POST['payment']['tax_type'];
        $sdf_order['payment'] = $payment['pay_app_id'];
        $member_id = kernel::single('b2c_user_object')->get_member_id();
        $sdf_order['member_id'] = $member_id;
        $sdf_order['area_id'] = $address_area[2]?$address_area[2]:$address['area'];
        $sdf_order['dis_point'] = floor($_POST['point']['score']);
        $arr_cart_object = $this->pagedata['aCart'];
        $this->set_header();
        echo $obj_total->order_total_method($this,$arr_cart_object,$sdf_order);exit;
    }
    /* 订单确认页end*/

    //widgets cart
    public function view(){
        $oCart = $this->app->model("cart_objects");
        $arr = array();
        $aData = $oCart->setCartNum( $arr );
        $this->pagedata['trading'] = $aData['trading'];
        $this->pagedata['cartCount'] = $aData['CART_COUNT'];
        $this->pagedata['cartNumber'] = $aData['CART_NUMBER'];
        $this->_common();

        // 购物车数据项的render 迷你购物车
        $this->pagedata['item_section'] = $this->mCart->get_item_render_view();

        // 购物车数据项的render
        $this->pagedata['item_goods_section'] = $this->mCart->get_item_goods_render_view();

        $tpl = 'site/cart/mini/view.html';
        $this->page($tpl, true);
    }

    public function loginBuy($isfastbuy=0)
    {
        //if( $this->check_login() ) {
        //    $this->begin( $this->gen_url( array('app'=>'b2c','act'=>'index','ctl'=>'site_cart') ) );
        //    $this->end( true,'您已经是登录状态！');
        //}

        if($isfastbuy){
          $_SESSION['pc_next_page'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'_check_checkout','arg0'=>'true'));
        }else{
          $_SESSION['pc_next_page'] = $this->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'checkout'));
        }

        $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_passport','act'=>'login','arg0'=>$_GET['mini_passport']));
        $this->redirect($url);
    }


    ////////////////////////////// 以下私有函数/////////////////////////////

    private function _cart_main($msg='',$json_type='all') {
        $aParams = $this->_request->get_params(true);
        if (!$msg) $msg = app::get('b2c')->_('购物车修改成功！');
        $obj_currency = app::get('ectools')->model('currency');
        $this->pagedata['ajax_html'] = $this->ajax_html;

        if($json_type == 'mini'){
            $this->ajax_html = false;
        }
        $this->_common(1,$aParams['is_fastbuy']);

        if( !$this->pagedata['is_empty'] ) {
            foreach($this->pagedata['aCart']['object']['gift']['order']  as $gift_key=>$gift_obj){
                $order_gift[$gift_key]['url'] = $gift_obj['url'];
                $order_gift[$gift_key]['thumbnail'] = $gift_obj['thumbnail'];
                $order_gift[$gift_key]['name'] = $gift_obj['name'];
                $order_gift[$gift_key]['spec_info'] = $gift_obj['spec_info'];
                $order_gift[$gift_key]['quantity'] = $gift_obj['quantity'];
                $order_gift[$gift_key]['price'] =$obj_currency->changer_odr($gift_obj['price']['price'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset);
            }

            $this->pagedata['aCart']['promotion_subtotal'] = $this->objMath->number_minus(array($this->pagedata['aCart']['subtotal'], $this->pagedata['aCart']['subtotal_discount']));
            $system_money_decimals = app::get('b2c')->getConf('system.money.decimals');
            $system_money_operation_carryset = app::get('b2c')->getConf('system.money.operation.carryset');
            switch ($json_type)
            {
                case 'mini':
                    $arr_json_data = array(
                        'sub_total'=>array(
                            'promotion_subtotal'=>$obj_currency->changer_odr($aCart['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'is_checkout'=>false,
                        'number'=>array(
                                'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                                'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                        'error_msg'=>$this->pagedata['error_msg'],
                    );
                    $view = 'site/cart/mini/view.html';
                break;
                case 'middle':
                    $arr_json_data = array(
                        'sub_total'=>array(
                            'subtotal_prefilter_after'=>$obj_currency->changer_odr($aCart['subtotal_prefilter_after'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                            'promotion_subtotal'=>$obj_currency->changer_odr($aCart['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                        ),
                        'is_checkout'=>false,
                        'number'=>array(
                                'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                                'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                        'error_msg'=>$this->pagedata['error_msg'],
                    );
                    if ($this->pagedata['aCart']['discount_amount_order'] > 0)
                        $arr_json_data['sub_total']['discount_amount_order'] = $obj_currency->changer_odr($this->pagedata['aCart']['discount_amount_order'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset);
                    else
                        $arr_json_data['sub_total']['discount_amount_order'] = 0;
                    $view = 'site/cart/middle_index.html';
                break;
                case 'coupon':
                    if(!empty($this->pagedata['aCart']['object']['coupon'])){
                        $return_coupon_data = array();
                        foreach($this->pagedata['aCart']['object']['coupon'] as $key=>$coupon_data){
                            if($coupon_data['used'] == 'true'){
                                $return_coupon_data[$key]['coupon'] = $coupon_data['coupon'];
                                $return_coupon_data[$key]['cpns_id'] = $coupon_data['cpns_id'];
                                $return_coupon_data[$key]['name'] = $coupon_data['name'];
                                $return_coupon_data[$key]['obj_ident'] = $coupon_data['obj_ident'];
                            }
                        }
                    }
                    $arr_json_data = array(
                        'success'=> $msg,
                        'data'=>$return_coupon_data,
                    );
                break;
                case 'all':
                default:
                    $arr_json_data = array(
                        'success'=> $msg,
                        'sub_total'=>array(
                            'subtotal_prefilter_after'=>$obj_currency->changer_odr($this->pagedata['aCart']['subtotal_prefilter_after'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                            'promotion_subtotal'=>$obj_currency->changer_odr($this->pagedata['aCart']['promotion_subtotal'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset),
                            'subtotal_gain_score' => $this->pagedata['aCart']['subtotal_gain_score'],
                        ),
                        'unuse_rule'=>$this->pagedata['unuse_rule'],
                        'is_checkout'=>false,
                        'edit_ajax_data'=>$this->pagedata['edit_ajax_data'],
                        'promotion'=>$this->pagedata['aCart']['promotion'],
                        'order_gift'=>$order_gift,
                        'error_msg'=>$this->pagedata['error_msg'],
                        'number'=>array(
                            'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                            'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                        ),
                    );
                    if ($this->pagedata['aCart']['discount_amount_order'] > 0)
                        $arr_json_data['sub_total']['discount_amount_order'] = $obj_currency->changer_odr($this->pagedata['aCart']['discount_amount_order'],$_COOKIE["S"]["CUR"],false,false,$system_money_decimals,$system_money_operation_carryset);
                    else
                        $arr_json_data['sub_total']['discount_amount_order'] = 0;
                    $view = 'site/cart/index.html';
                break;
            }
        }else{
            $arr_json_data = array(
                'is_empty' => 'true',
                'number'=>array(
                    'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                    'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                ),
            );
       }

        $md5_cart_info = kernel::single('b2c_cart_objects')->md5_cart_objects();
        $arr_json_data['md5_cart_info'] = $md5_cart_info;
        if($json_type == 'mini'){
            $this->pagedata = $arr_json_data;
            $this->view();
        }else{
            echo json_encode($arr_json_data);exit;
        }
    }

    public function _common($flag=0,$is_fastbuy=false) {
        // 购物车数据信息
        $aData = $this->_request->get_params(true);
        if($is_fastbuy){
            $aData['is_fastbuy'] = $is_fastbuy;
        }
        $aCart = $this->mCart->get_objects($aData);

        $this->_item_to_disabled( $aCart,$flag ); //处理购物扯删除项
        $this->pagedata['aCart'] = $aCart;

        if( $this->show_gotocart_button ) $this->pagedata['show_gotocart_button'] = 'true';

        if( $this->ajax_update === true ) {
            foreach(kernel::servicelist('b2c_cart_object_apps') as $object) {
                if( !is_object($object) ) continue;

                //应该判断是否实现了接口
                if( !method_exists( $object,'get_update_num' ) ) continue;
                if( !method_exists( $object,'get_type' ) ) continue;

                $this->pagedata['edit_ajax_data'] = $object->get_update_num( $aCart['object'][$object->get_type()],$this->update_obj_ident );
                if( $this->pagedata['edit_ajax_data'] ) {
                    //$this->pagedata['edit_ajax_data'] = json_encode( $this->pagedata['edit_ajax_data'] );
                    if( $object->get_type()=='goods' ) {
                        $this->pagedata['update_cart_type_godos'] = true;
                        if( !method_exists( $object,'get_error_html' ) ) continue;
                        $this->pagedata['error_msg'] = $object->get_error_html( $aCart['object']['goods'],$this->update_obj_ident );
                    }
                    break;
                }
            }
        }


        // 购物车是否为空
        $this->pagedata['is_empty'] = $this->mCart->is_empty($aCart);
        //ajax_html 删除单个商品是触发
        if($this->ajax_html && $this->mCart->is_empty($aCart)) {
            $arr_json_data = array(
                'is_empty' => 'true',
                'number'=>array(
                    'cart_number'=>$this->pagedata['aCart']['_cookie']['CART_NUMBER'],
                    'cart_count'=>$this->pagedata['aCart']['_cookie']['CART_COUNT'],
                ),
            );
            $this->pagedata = $arr_json_data;
            $this->page('site/cart/cart_empty.html', true);
            return ;
        }

        // 购物车数据项的render
        $this->pagedata['item_section'] = $this->mCart->get_item_render();

        // 购物车数据项的render
        $this->pagedata['item_goods_section'] = $this->mCart->get_item_goods_render();

        // 优惠信息项render
        $this->pagedata['solution_section'] = $this->mCart->get_solution_render();

        //未享受的订单规则
        $this->pagedata['unuse_rule'] = $this->mCart->get_unuse_solution_cart($aCart);

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
    }


    /*
     * item 禁用的数据类型如下：
      $_SESSION['cart_objects_disabled_item']
      array(
      'goods' => array(
        'goods_12_23' => array(
            'gift' => array(
                0 => true,
                3 => true,
                ),
            ),
        ),
      );
    */
    private function _item_to_disabled( &$aCart,$flag ) {

        foreach( kernel::servicelist('b2c_cart_object_apps') as $object ) {
            if( !is_object($object) ) continue;
            $o[$object->get_type()] = $object;
        }

        $arr_cart_disabled_session = $_SESSION['cart_objects_disabled_item'];
        foreach( (array)$aCart['object'] as $_obj_type => $_arr_by_obj_type ) {
            $tmp = $arr_cart_disabled_session[$_obj_type];
            if( isset($arr_cart_disabled_session[$_obj_type]) ) {
                if( !$o[$_obj_type] ) continue;
                if( !method_exists( $o[$_obj_type],'apply_to_disabled' ) ) continue;
                $aCart['object'][$_obj_type] = $o[$_obj_type]->apply_to_disabled( $_arr_by_obj_type, $tmp, $flag );
                $_SESSION['cart_objects_disabled_item'][$_obj_type] = $tmp;
            } else {
                if( $flag )
                    unset($_SESSION['cart_objects_disabled_item'][$obj_type]);
            }
        }
    }


    private function _v_cart_object ($temp, $row,$flag=false) {
        if( !$temp['quantity']) {
            if( isset($row['params']['adjunct']) && is_array($row['params']['adjunct']) ) {
                foreach( $row['params']['adjunct'] as $adjunct ) {
                    if( !isset($adjunct['adjunct']) || !is_array($adjunct['adjunct']) ) continue;
                    foreach( $adjunct['adjunct'] as $p_id => $p_quantity ) {
                        if( !isset($temp['adjunct'][$adjunct['group_id']][$p_id]) ) { $flag = false; continue; }
                        #if($temp[$adjunct['group_id']][$p_id]['quantity']!=$p_quantity) {
                            $this->update_obj_ident['index'] = array('adjunct');
                            $this->update_obj_ident['id'] = $p_id;
                            $flag = false;
                            break 2;
                        #}
                    }
                }
            }
            return $flag;
        }
        return $flag;
    }

    /*
     * 凑单
     * */
    function fororder(){
        $this->_response->set_header('Cache-Control', 'no-store, no-cache');
        $fororder_setting = app::get('b2c')->getConf('cart_fororder_setting');
        //每个TAB的商品数量
        $limit = $fororder_setting['fororder']['nums'];
        //价格区间 TAB
        $fororder_filter = $fororder_setting['fororder']['filter'];
        foreach($fororder_filter as $tab_key=>$tab_value){
            if(empty($tab_value['price_min']) || !$tab_value['price_min']){
                $new_fororder_tab[$tab_key]['tab_name'] = $tab_value['price_max'].'元以下';
            }elseif(empty($tab_value['price_max']) || $tab_value['price_max'] == 999999){
                $new_fororder_tab[$tab_key]['tab_name'] = $tab_value['price_min'].'元以上';
            }else{
                $new_fororder_tab[$tab_key]['tab_name'] = $tab_value['price_min'].'-'.$tab_value['price_max'].'元';
            }
            $new_fororder_tab[$tab_key]['tab_filter'] = $tab_value['price_min'].'-'.$tab_value['price_max'];
        }
        $this->pagedata['fororder_tab'] = $new_fororder_tab;

        if(isset($_POST['tab_name'])){
            $tab_name=$_POST['tab_name'];
            $view = 'site/cart/cart_fororder_item.html';
        }else{
            $new_tab_name = current($new_fororder_tab);
            $tab_name=$new_tab_name['tab_filter'];
            $view = 'site/cart/cart_fororder.html';
        }

        //获取商品
        if(!cachemgr::get('fororder_goods_data'.$tab_name,$list)){
            cachemgr::co_start();
            $filter = $this->fororder_filter($tab_name);
            $goods_model = app::get('b2c')->model('goods');
            $aGoods_list = $goods_model->getList("store,spec_desc,goods_id,name,image_default_id,price,nostore_sell",$filter,0,$limit,'price asc,last_modify desc');
            foreach($aGoods_list as $goods_key=>$goods){
                $goodsids[]= $goods['goods_id'];
                $list[$goods['goods_id']] = $goods;
            }
            $products = app::get('b2c')->model('products')->getList('goods_id,product_id,store,freez',array('goods_id'=>$goodsids,'marketable'=>'true','is_default'=>'true'),0,-1,'price asc');
            foreach( (array)$products as $pk=>$row ){
                $goods = $list[$row['goods_id']];
                if(($goods['store'] === null || $row['store'] - $row['freez']) > 0 || $goods['nostore_sell'] ){
                    $list[$row['goods_id']]['product_id'] = $row['product_id'];
                }else{
                    unset($list[$row['goods_id']]);
                }
            }
            cachemgr::set('fororder_goods_data'.$tab_name, $list, cachemgr::co_end());
        }

        $i = 1;
        foreach($list as $goods_item){
            if($i <= 4){
                $aGoods_list_before[] = $goods_item;
                $i ++;
            }else{
                $aGoods_list_last[] = $goods_item;
                $i++;
                if($i == 9) $i=1;
            }
        }

        $aGoods_list['before'] = $aGoods_list_before;
        $aGoods_list['last'] = $aGoods_list_last;

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['goods_list'] = $aGoods_list;
        echo $this->fetch($view);
    }

    //检查当前TAB是否有商品
    function _check_fororder($tab_name,&$msg){
        $filter = $this->fororder_filter($tab_name);
        $goods = app::get('b2c')->model('goods')->getList('goods_id',$filter);
        if(!$goods){
            $msg = 0;
            return false;
        }
        $msg = count($goods);
        return true;
    }

    //凑单TAB条件，现在默认为使用tab名称0-30作为条件
    function fororder_filter($fororder_tab_name){
        if(is_array($fororder_tab_name)){
            $price[0] = $fororder_tab_name['price_min'];
            $price[1] = $fororder_tab_name['price_max'];
        }else{
            $price = explode('-',$fororder_tab_name);
        }
        $filter['price'] = $price;
        $filter['marketable'] = 'true';
        return $filter;
    }

    function set_header(){
        header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
        header('Progma: no-cache');
        header('Content-Type:text/html; charset=utf-8');
    }
}

