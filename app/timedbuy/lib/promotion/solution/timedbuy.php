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
class timedbuy_promotion_solution_timedbuy
{
    
    public $name = "限时抢购"; // 名称
    public $type = 'goods'; //默认goods
    public $desc_pre = '限时抢购';
    public $desc_post = '';
    private $description = '';
    
    
    /**
     * 优惠方案模板
     * @param array $aConfig // 设置信息(修改的时候传入)
     * @return string // 返回要输出的模板html
     */
    public function config($aData = array()) {
        $o = kernel::single('timedbuy_frontpage');
        $forenotice['status'] = array(
                                        '1'=>'是',
                                        '2'=>'否',
                                    );
        $o->pagedata['config'] = $aData;
        $forenotice['timeh'] = range(0,23);
        $forenotice['timei'] = range(0,59);
        $forenotice['times'] = range(0,59);
        $o->pagedata['forenotice'] = $forenotice;
        $o->pagedata['prename'] = 'action_solution[timedbuy_promotion_solution_timedbuy]';
        return $o->fetch('admin/promotion/solution/timedbuy.html');
        //action_solution[b2c_promotion_solutions_addscore][gain_score]
    }

    /**
     * 优惠方案应用
     *
     * @param array $object  // 引用的一个商品信息
     * @param array $aConfig // 优惠的设置
     * @param array $cart_object // 购物车信息(预过滤的时候这个为null)
     * @return void // 引用处理了,没有返回值
     */
    public function apply(&$object,$aConfig,&$cart_object = null) {
        
        if(is_null($cart_object)) { // 商品预过滤
            if(trim($aConfig['price'])) {
                $object['obj_items']['products'][0]['price']['buy_price'] = trim($aConfig['price']);
            }
            
            //注释掉了。加上之后购物车中disacount_prefilter 中没有数据  bug：0021936
            #$object['obj_items']['products'][0]['price']['member_lv_price'] = trim($aConfig['price']);
        } else {// 购物车里的处理
            
        }
        $this->setString($aConfig);
    }
    
    
    
    
    /**
     * 优惠方案应用
     *
     * @param array $object  // 引用的一个商品信息
     * @param array $aConfig // 优惠的设置
     * @param array $cart_object // 购物车信息(预过滤的时候这个为null)
     * @return void // 引用处理了,没有返回值
     */
    public function apply_order(&$object, &$aConfig,&$cart_object = null) {
       return false;
    }
    
    
    public function setString($aData) {
        $this->description = '限时抢购!';
    }
    
    public function getString() {
        return $this->description;
    }
    
    
    
    public function get_status() {
        return true;
    }
    
    public function allow( $is_order ) {
        return 'goods';
    }
    
    
    public function get_solution_after( $solution,$aConfig,$arrGoods ) {
        $timedbuy = $show_button = 'false';
        if( $aConfig['from_time']<time()  && $aConfig['to_time']>time() ) {  //促销时间范围内
            $return = true;
        } else if( $aConfig['from_time']>time() ) {  //促销开始前 计算是否启用了预告
            if( $solution['forenotice']['status']==1 ) {
                $forenotice = $solution['forenotice'];
                $forenotice_time = $forenotice['timeh']*3600;
                $forenotice_time += $forenotice['timei']*60;
                $forenotice_time += $forenotice['times'];
                if( time()>=$aConfig['from_time']-$forenotice_time ) {
                    $return = true;
                    $show_button = 'true';
                }
            }
        }
        
        //获取购买信息
        kernel::single('timedbuy_cart_object_goods')->_get_kvstore( $aConfig,$arrGoods['goods_id'],$member_num,$num,$config );
        if( $config['quantity'] && $config['quantity']<=$num ) {
            $timedbuy = 'true';
        }
        return $return ? array('price'=>$solution['price'],'show_button'=>$show_button,'timebuy_over'=>$timedbuy) : array();
    }
    /**
     * 校验参数是否正确
     * @param mixed 需要校验的参数
     * @param string error message
     * @return boolean 是否成功
     */
    public function verify_form($data=array(), &$msg='')
    {
        if (empty($data['action_solution']['timedbuy_promotion_solution_timedbuy']['price']))
        {
            $msg = app::get('b2c')->_('请指定限时抢购价格！');
            return false;
        }
        if($data['action_solution']['timedbuy_promotion_solution_timedbuy']['quantity']) {
            $where = kernel::single('b2c_sales_goods_process')->filter($data['conditions']);
            $goods = kernel::database()->selectrow("SELECT MIN(store) AS min_store FROM sdb_b2c_goods WHERE ". $where);
            if($goods['min_store'] != null && $goods['min_store'] < $data['action_solution']['timedbuy_promotion_solution_timedbuy']['quantity']) {
                $msg = app::get('timedbuy')->_('限时抢购商品数量不能大于实际库存');
                return false;
            }
        }
        return true;
    }
}