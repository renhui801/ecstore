<?php
/**
 * cps_linklog
 * 前台推广链接关联会员、订单、来源第三方类linklog控制层类
 *
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_linklog Jun 28, 2011  9:54:56 AM ever $
 */
class cps_linklog {

    /**
     * 操作识别号
     * @access private
     * @var string
     */
    private $_ident_op = '#r-p';

    /**
     * app对象
     * @access private
     * @var object
     */
    private $app = null;

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
     * 保存会员、订单与来源的关联信息函数
     * @access public
     * @param int $id 会员id/订单id
     * @param string $type 类型(member/order)
     * @return boolean
     * @version 1 Jun 28, 2011 创建
     */
    public function set_arr($id = 0, $type = '') {
        //id与类型不为空
        if(empty($id) || empty($type)) {
            return false;
        }

        //获取当前来源相关cookie信息函数
        $this->get_refer($data);

        //首次来源id不为空
        if(empty($data['refer_id'])) {
            return false;
        }

        //首次来源id是否为联盟商
        $user = $this->app->model('users')->dump(array('union_id' => $data['refer_id']), 'u_id');
        if (empty($user['u_id'])) {
            return false;
        }

        /*j
        //判断本次来源是否为联盟商
        $user = $this->app->model('users')->dump(array('union_id' => $data['c_refer_i']), 'u_id');
        if (empty($user['u_id'])) {
            return false;
        }
        */

        $data['target_id']   = $id;
        $data['target_type'] = $type;

        return $this->app->model('linklog')->save($data);
    }

    /**
     * 获取会员、订单与来源的关联信息函数
     * @access public
     * @param int $id 会员id/订单id
     * @param string $type 类型(member/order)
     * @return array
     * @version 1 Jun 28, 2011 创建
     */
    public function get_arr($id = 0, $type = '') {
        //id与类型不为空
        if(empty($id) || empty($type)) {
            return false;
        }

        //查询条件
        $filter = array(
            'target_id'=> $id,
            'target_type' => $type,
        );

        //根据查询条件获取关联信息
        $arr = $this->app->model('linklog')->dump($filter, '*');
        return $arr;
    }

    /**
     * 获取当前来源相关cookie信息函数
     * @access public
     * @param array &$data 来源信息数组
     * @version 1 Jun 28, 2011 创建
     */
    private function get_refer(&$data) {
        //cookie中存在来源信息
        if(isset($_COOKIE['S']['FIRST_REFER'])||isset($_COOKIE['S']['NOW_REFER'])){
            //首次来源
            $firstR = json_decode(stripslashes($_COOKIE['S']['FIRST_REFER']),true);
            //当前来源
            $nowR = json_decode(stripslashes($_COOKIE['S']['NOW_REFER']),true);
            $data['refer_id'] = urldecode($firstR['ID']);
            $data['refer_time'] = $firstR['DATE']/1000;
            $data['c_refer_id'] = urldecode($nowR['ID']);
            $data['c_refer_url'] = $nowR['REFER'];
            $data['c_refer_time'] = $nowR['DATE']/1000;
            $data['refer_url'] = $firstR['REFER'] ? $firstR['REFER'] : $data['c_refer_url'];
        }
    }
}
