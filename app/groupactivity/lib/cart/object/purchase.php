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
class groupactivity_cart_object_purchase implements b2c_interface_cart_object
{
    private $app;
    private $member_ident; // 用户标识
    private $oCartObject;
    private $_obj_order;
    private $__gourp_id;

    /**
     * 构造函数
     *
     * @param $object $app  // service 调用必须的
     */
    public function __construct(&$app) {
        $this->_obj_order = array('get'=>1,'del'=>99);
        $this->app = $app;
        $this->arr_member_info = kernel::single('b2c_cart_objects')->get_current_member();
        $this->member_ident = kernel::single("base_session")->sess_id();
    }

	/**
	 * 购物车是否需要验证库存
	 * @param null
	 * @return boolean true or false
	 */
	public function need_validate_store() {
		return false;
	}

    public function get_order( $type='get' ) {
        return $this->_obj_order[$type];
    }
    public function get_type() {
        return 'purchase';
    }

	public function get_part_type() {
		return array('goods');
	}

    public function add_object($aData) {
        return $this->set_session( $aData );
    }


    public function update($sIdent,$quantity) {
        return false;
    }


    public function get($sIdent = null,$rich = false) {
        return false;
    }

	/**
	 * 得到失败应该返回的url - app 数组
	 * @param array
	 * @return array
	 */
	public function get_fail_url($data=array())
	{
		return array('app'=>'groupactivity', 'ctl'=>'site_cart', 'act'=>'index');
	}

    public function getAll($rich = false) {
		if(kernel::single("b2c_cart_object_goods")->get_cart_status()) {
            return array();
        } else {
			if( !($goods=$this->_check()) ) {
				if( $this->is_group() ) {
					kernel::single("site_controller")->begin( );
					kernel::single("site_controller")->end( false,$this->error_html );exit;
				} else
					return false;
			}
			$result = array(
				'obj_ident' => $this->_generateIdent($goods),
				'obj_type' => 'goods',
				'quantity' => $goods['num'],
			);

			$o = kernel::single('b2c_cart_object_goods');
			$arr = $o->_get_products(array($goods['product_id']));
			foreach( (array)$arr as $key => $row ) {
				$arr[$key]['name'] = $row['name'].'(团购)';
			}
			$arr = $arr[$goods['product_id']];
			$purchase = kernel::single('groupactivity_purchase')->_get_dump_data( $arr['goods_id'] );
			if( !$this->promotion( $arr,$goods['num'] ) ) return false;
			$o->__get_score( $arr );

			//积份设置
			if(!isset($this->site_score_policy) && empty($this->site_score_policy)) {
				$this->site_score_policy = app::get('b2c')->getConf('site.get_policy.method');
			}
			if( isset( $purchase['score'] ) ){
				if($this->site_score_policy==1 && isset($purchase['score'])) {
					//后台设置不使用积分的情况
					$purchase['score'] = 0;
				}
				$arr['gain_score'] = $purchase['score'];
			}
			if( $arr['is_free_shipping'] ) $result['is_free_shipping'] = $arr['is_free_shipping'];
			$result['obj_items']['products'][] = $arr;
			app::get('b2c')->controller('site_cart')->show_gotocart_button = false;
			return array($result);
		}
    }

    /*
     * 删除购物车项 参数应用
     */
    public function delete_item( &$aCart )
    {
        if( !$this->get_session() ) return false;
        if( !is_array($aCart['object']) )return false;

        foreach( $aCart['object'] as $key => $val ) {
            if( $key!=$this->get_type() ) unset($aCart['object'][$key]);
        }
        foreach( $aCart as $key => $val ) {
            if( !in_array($key,array('object','_cookie')) ) unset( $aCart[$key] );
        }

        $aCart['object']['goods'] = $aCart['object'][$this->get_type()];
        if( $aCart['object'][$this->get_type()]['is_free_shipping'] )
            $aCart['is_free_shipping'] = true;
        $arr = $this->count($aCart);

        $aCart = array_merge($aCart,(array)$arr);
        $aCart['_cookie'] = $aCart['object'];
        $aCart['groupactivity'] = 1;

        unset($aCart['object'][$this->get_type()]);

    }
    #End Func


    public function delete($sIdent = null) {
        if( $this->get_session() ) {
            $this->unset_session();
            return 'false';
        }
        return true;
    }


    public function deleteAll() {
        return $this->delete();
    }


    public function count(&$aData) {
        if(empty($aData['object'][$this->get_type()])) return false;
        $aResult = array(
                      'subtotal_weight'=>0,
                      'subtotal'=>0,
                      'subtotal_price'=>0,
                      'subtotal_consume_score'=>0,
                      'subtotal_gain_score'=>0,
                      'discount_amount_prefilter'=>0,
                      'discount_amount_order'=>0,
                      'discount_amount'=>0,
                      'items_quantity'=>0,
                      'items_count'=>0,
                   );

        foreach($aData['object'][$this->get_type()] as &$row) {
            $this->_count($row);

            $aResult['subtotal_consume_score'] += $row['subtotal_consume_score'];
            $aResult['subtotal_gain_score'] += $row['subtotal_gain_score'] + $row['sales_score_order'];

            $aResult['subtotal'] += $row['subtotal'];
            $aResult['subtotal_price'] += $row['subtotal_price'];

            #if(!(isset($aData['is_free_shipping']) && $aData['is_free_shipping'])) { // 全场免运费
                $aResult['subtotal_weight'] += $row['subtotal_weight'];
            #}

            if( isset($row['is_free_shipping']) )  {
                $aResult['is_free_shipping'] = true;
            }

            $aResult['discount_amount_prefilter'] += $row['discount_amount_prefilter'];

            $aResult['discount_amount_order'] += $row['discount_amount_order'];
            $aResult['discount_amount'] += $row['discount_amount_cart'] ;
            $aResult['items_quantity'] += $row['quantity'];
            $aResult['items_count']++;
        }
        return $aResult;
    }

    private function _count( &$aData ) {
        $o = kernel::single('ectools_math');
        // 重新统计时将以下值 置为0
        $aData['subtotal_consume_score'] = 0;
        $aData['subtotal_gain_score'] = 0;
        $aData['subtotal'] = 0;
        $aData['subtotal_price'] = 0;
        $aData['subtotal_weight'] = 0;
        $aData['discount_amount'] = 0;
        $aData['discount_amount_prefilter'] = 0;
        foreach($aData['obj_items']['products'] as $key=>&$row) {
            if($key != 0) break;
            $aResult = $this->_count_product($row);

            $aData['obj_items']['products'][$key]['subtotal'] = $o->number_multiple( array($aResult['subtotal'] * $aData['quantity']) );
            $aData['subtotal_consume_score'] += $aResult['subtotal_consume_score'];
            $aData['subtotal_gain_score'] += $aData['sales_score'] + $aResult['subtotal_gain_score'];
            $aData['subtotal'] += $o->number_multiple( array($aResult['subtotal'],$aData['quantity']) );
            $aData['subtotal_price'] += $aResult['subtotal_price'] * $aData['quantity'];
            $aData['subtotal_weight'] += $o->number_multiple( array($aResult['subtotal_weight'],$aData['quantity']) );
            $aData['discount_amount_prefilter'] += ($aResult['subtotal'] - $aResult['subtotal_current']);
        }


        // 数量
        $aData['subtotal_consume_score'] *= $aData['quantity'];
        $aData['subtotal_gain_score'] *= $aData['quantity'];
    }

    private function _count_product(&$row){
        $aResult = array(
                      'subtotal_weight'=>0,
                      'subtotal'=>0,
                      'subtotal_price'=>0,
                      'subtotal_consume_score'=>0,
                      'subtotal_gain_score'=>0,
                      'subtotal_current'=>0,
               );
        $aResult['subtotal_weight'] += $row['weight'] * $row['quantity'];
        $aResult['subtotal'] += $row['price']['member_lv_price'];// * $row['quantity']; // 按商品价格
        $aResult['subtotal_price'] += $row['price']['price'];// * $row['quantity']; // 按商品价格
        $aResult['subtotal_consume_score'] += $row['consume_score'] * $row['quantity'];
        $aResult['subtotal_gain_score'] = $row['gain_score']; //* $row['quantity'];

        $aResult['subtotal_current'] += $row['price']['buy_price']; // 按实际购买价格
        return $aResult;
    }


    public function apply_to_disabled( $data,$session,$flag ) {
        return false;
    }



    public function set_session($data) {
        $data = $data[$this->get_type()];
        if( !$data ) return false;
        kernel::single("base_session")->start();
        $_SESSION['groupactivity'] = $data;
        return true;
    }


    public function unset_data() {
        if( !$this->is_group() )
            $this->unset_session();
    }


    public function get_session() {
        kernel::single("base_session")->start();
        return $_SESSION['groupactivity'];
    }

    public function unset_session() {
        kernel::single("base_session")->start();
        unset($_SESSION['groupactivity']);
    }

    public function get_group_id() {
        return $this->__gourp_id ? $this->__gourp_id : 1;
    }

    public function _check() {
        $data = $this->get_session();

        if( !$data['group'] ) return false;
        $goods = $data['goods'];
        if( !$goods['product_id'] || !$goods['goods_id'] ) {
            $this->error_html = '参数错误！';
            return false;
        }
        $filter = array( 'gid'=>$goods['goods_id']);
        $filter['end_time|than'] = time();
        $purchase = $this->app->model('purchase')->getList( '*',$filter );
        $goodsinfo = app::get('b2c')->model('goods')->getList('nostore_sell',array('goods_id'=>$goods['goods_id']));
        $purchase['0']['nostore_sell'] = $goodsinfo['0']['nostore_sell'];
        #if( !$arr || !is_array($arr) ) {
        #    $this->error_html = '数据错误！！';
        #    return false;
        #}
        reset ($purchase);
        $purchase = current( $purchase );
        $this->__gourp_id = $purchase['act_id'];
        #不再活动时间范围内
        if( $purchase['start_time']>time() || $purchase['end_time']<time() || $purchase['state']!=='2' ) {
            $this->error_html = '不再活动时间范围之内';
            return false;
        } else if( $purchase['act_open']!='true') {
            $this->error_html = '该商品团购活动没有开启';
            return false;
        }

        $arrMember = kernel::single('b2c_frontpage')->get_current_member();

        if( $purchase['alluser']=='false') {
            if(!$arrMember['member_id'] ) {
                $_SESSION['groupactivity-redirect'] = $data;
                $_SESSION['next_page'] = $this->app->controller('site_cart')->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'checkout') );
                kernel::single('b2c_frontpage')->redirect(array('app'=>'b2c','ctl'=>'site_cart','act'=>'loginbuy','arg0'=>'1'));
            }

            $member_lv_ids = explode(',',$purchase['member_lv_ids']);
            if( !in_array($arrMember['member_lv'],(array)$member_lv_ids) ) {
                $this->error_html = '您所在会员等级不符！';
                return false;
            }
        }
        if(intval($goods['num'])<1){
            $this->error_html = '输入数量错误！';
            return false;
        }
        if( $purchase['max_buy'] ) {
            if( ($purchase['buy'] + $goods['num']) > $purchase['max_buy'] ) {
                $this->error_html = '商品库存不足！还有'. ($purchase['max_buy']-$purchase['buy']-$goods['mum']) .'个!';
                return false;
            }
        }
        $arr_product = app::get('b2c')->model('products')->getList('*',array('product_id'=>$data['goods']['product_id']));
        $arr_product = $arr_product[0];

        if( isset( $arr_product['store'] ) && $purchase['nostore_sell']!=1 ){
            if( $data['goods']['num']>($arr_product['store']-floatval($arr_product['freez']) ) ) {
                $this->error_html = '商品库存不足！';
                return false;
            }
        }
        if( $purchase['alluser']=='true' ) {
            if( $purchase['orderlimit'] && $data['goods']['num']>$purchase['orderlimit'] ) {
                $this->error_html = '超出每单限购数量！';
                return false;
            }
        } else {
            $this->get_purchase_sum($purchase,$sum);
            if( $purchase['userlimit'] ) {
                if( $purchase['userlimit']<$sum+$data['goods']['num'] ) {
                    $p = $purchase['userlimit']-$sum;

                    $this->error_html = $p ? ("超出每人限购数量！您还可以购买的数量为{$p}!") : '对不起！您已达到限购数量！';
                    return false;
                }
            }
        }

        return $goods;
    }

    public function get_purchase_sum( &$purchase,&$sum ) {
        $arr = $this->app->model('order_act')->getList( 'order_id,quantity,createtime',array('act_id'=>$purchase['act_id'],'member_id'=>$this->arr_member_info['member_id']) );
        $tmp = $arr_orders = $arr_order_id = $arr_dead_orders = array();

        foreach( $arr as $row ) {
            $arr_order_id[] = $row['order_id'];
        }

        $arr_orders = app::get('b2c')->model('orders')->getList( 'order_id,status,createtime',array('order_id'=>$arr_order_id) );

        foreach( $arr_orders as $row ) {
            if( $row['status']=='dead')
                $arr_dead_orders[$row['order_id']] = true;
        }
        foreach( $arr as $row ) {
            if( !$arr_dead_orders[$row['order_id']] && ($purchase['last_modified'] && $row['createtime'] && $purchase['last_modified']<=$row['createtime']))
                $tmp[] = $row['quantity'];
        }

        $sum = array_sum($tmp);
    }

    private function _generateIdent($aData) {
        return "puchase_{$aData['goods_id']}_{$aData['product_id']}";
        return false;
    }

    private function promotion( &$arr,$num ) {

        $data = $this->app->model('purchase')->getList( '*',array('gid'=>$arr['goods_id'],'end_time|than'=>time()) );
        if( $data && is_array($data) ) {
            reset( $data );
            $data = current( $data );
            if( $data['pro_type']==1 && $num>=$data['postage'] ) {
                $arr['is_free_shipping'] = true;
            }
            $arr['price']['buy_price'] = $arr['price']['member_lv_price'] = $data['price'];

            return true;
        } else {
            return false;
        }
    }

    private function is_group() {
        $params = kernel::single("base_component_request")->get_params(true);
        if( $params['extends_args'] ) {
            $a = json_decode($params['extends_args'], 1);
            if( $a['get'] ) {
                $a['get'][0]=='group';
                return true;
            }
        } else {
            if( $params[0]=='group' ) return true;
        }
        return false;
    }



}
