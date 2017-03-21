<?php
/**
 * cps_user_orderprofit
 * 前台联盟商关联订单佣金第三方类orderprofit控制层类
 *
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_user_orderprofit Jun 28, 2011  9:54:56 AM ever $
 */
class cps_user_orderprofit {
    private $_cps_account_type = 'cpsuser';
    private $_cps_profit_rate_skey = 'profitRate';

    /**
     * 初始化构造方法
     * @access public
     * @param object $app
     * @version 1 Jun 28, 2011 创建
     */
    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * service调用方法执行优先级顺序方法
     * @access public
     * @return int
     * @version 1 Jun 28, 2011 创建
     */
    public function get_order(){
        $order = 1;
        return $order;
    }
    
    /**
     * 订单创建成功后生成联盟商关联订单佣金记录方法
     * @access public
     * @param array $order_data 订单信息数组
     * @return boolean
     * @version 1 Jun 28, 2011 创建
     */
    public function generate($order_data){
        $this->db = kernel::database();
        $referRow = $this->db->selectrow("select refer_id,refer_url from sdb_cps_linklog where target_id =".$order_data['order_id']." and target_type ='order'");

        //判断当前订单是否是联盟商推广订单
        if(empty($referRow['refer_id'])) {
            return false;
        }
        
        $referRow['refer_url'] = $referRow['refer_url'] ? $referRow['refer_url'] : '';
        
        //根据来源联盟标示获取联盟商id和联盟商账号
        $unionRow = $this->db->selectrow('select u_id,u_name from sdb_cps_users where union_id =' . $referRow['refer_id']);
        
        $data['u_id'] = $unionRow['u_id'];
        $data['u_name'] = $unionRow['u_name'];
        $data['order_id'] = $order_data['order_id'];
        $data['refer_url'] = $referRow['refer_url'];
        $data['order_cost'] = $order_data['cost_item'];
        $data['money'] = 0;
        //获取后台设置的佣金比例
        $profitRateRow = unserialize($this->app->model('setting')->getValueByKey($this->_cps_profit_rate_skey));
        if($profitRateRow['type'] == 'whole'){//统一比例
            $data['money'] = $order_data['cost_item']*$profitRateRow['value']/100;
        }else if($profitRateRow['type'] == 'cat'){//按分类比例
            //获取订单商品及所属分类
            $sql = "select oi.item_id,oi.amount,gc.cat_path,gc.cat_id,gc.parent_id 
                from sdb_b2c_order_items oi,sdb_b2c_goods g,sdb_b2c_goods_cat gc,sdb_b2c_order_objects oo 
                where oi.order_id='".$data['order_id']."' AND oi.goods_id=g.goods_id AND g.cat_id=gc.cat_id 
                    AND oi.obj_id=oo.obj_id AND oo.obj_type='goods'";
            $items = $this->db->select($sql);
            //按不同的分类比例对每个货品计算佣金后累加
            foreach($items as $k=>$v){
                //获取商品对应的一级分类
                if ($v['parent_id'] == 0) {
                    $cat_id = $v['cat_id'];
                } else {
                    $cat_ids = explode(',',$v['cat_path']);
                    $cat_id = $cat_ids[1];
                }
                //获取分类比例，在分类路径数组中，倒数第二个元素为一级分类的cat_id
                $aCatRate = $profitRateRow['value'][$cat_id] ? ($profitRateRow['value'][$cat_id]/100) : 0;
                //累加佣金
                $data['money'] += $v['amount'] * $aCatRate;
            }
        }
        $data['addtime'] = $order_data['createtime'];
        $data['status'] = 0;

        return kernel::single('cps_mdl_userorderprofit')->save($data);

    }

}