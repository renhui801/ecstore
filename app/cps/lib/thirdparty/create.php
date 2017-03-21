<?php
/**
 * cps_thirdparty_create
 * 第三方CPS关联订单佣金记录类
 *
 * @uses
 * @package
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_thirdparty_create Aug 1, 2011  2:43:39 PM ever $
 */
class cps_thirdparty_create {
    /**
     * APP对象
     * @access private
     * @var object
     */
    private $app = null;

    /**
     * 第三方CPS订单记录
     * @access private
     * @var object
     */
    private $mdlTpo = null;

    /**
     * 构造方法，初始化
     * @access public
     * @param object $app
     * @version 1 Aug 1, 2011
     */
    public function __construct($app) {
        $this->app = $app;
        $this->mdlTpo = kernel::single('cps_mdl_thirdparty_orders');
    }

    /**
     * service调用方法执行优先级顺序方法
     * @access public
     * @return int
     * @version 1 Aug 1, 2011
     */
    public function get_order() {
        $order = 2;
        return $order;
    }

    /**
     * 订单创建成功后生成联盟商关联订单佣金记录方法
     * @access public
     * @param array $order 订单数据
     * @return boolean
     * @version 1 Aug 1, 2011
     */
    public function generate($order) {
        //首次来源
        $json = stripslashes($_COOKIE['S']['FIRST_REFER']);
        $refer = json_decode($json, true);
        //返回值
        $rtn = false;

        //根据来源ID进行处理
        switch ($refer['ID']) {
            case 'emar':
                $rtn = $this->emar($order, $refer);
                break;
            default:
                break;
        }
        return $rtn;
    }

    /**
     * 亿起发订单处理，本地存入数据库，调用亿起发接口
     * @access private
     * @param array $order 订单数据
     * @param array $refer 订单来源
     * @return boolean
     */
    private function emar($order, $refer) {
        //存入亿起发订单记录
        $data = array(
            'order_id' => $order['order_id'],
            'src' => 'emar',
            'createtime' => $order['createtime'],
            'order_cost' => $order['cost_item'],
            'url' => $refer['refer_url'] . '',
            'status' => '0',
            'params' => array(
                'cid' => $refer['cid'],
                'wi' => $refer['wi'],
            ),
        );
        //本地存入数据库
        $rtn = $this->mdlTpo->save($data);

        //调用亿起发接口
        $url = 'http://o.yiqifa.com/servlet/handleCpsIn?cid=' . $refer['cid'] . '&wi=' . $refer['wi'] . '&on=' . $order['order_id'] .
        	'&ta=1&pp=' . $order['cost_item'] . '&sd=' . date('Y-m-d H:i:s', $order['createtime']) . '&encoding=UTF-8';
        $rs = file_get_contents($url);
        return $rtn;
    }
}