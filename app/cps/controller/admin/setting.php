<?php
/**
 * cps_ctl_admin_setting
 * 基础配置控制层类
 *
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_setting Jun 20, 2011  3:34:28 PM ever $
 */
class cps_ctl_admin_setting extends desktop_controller {

    public $workground = 'cps_center';

    /**
     * 构造初始化方法
     * @param object $app
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 基础配置(佣金比例)显示
     * @access public
     * @version 3 Aug 5, 2011
     */
    public function index() {
        //CPS配置模型
        $mdlSetting = kernel::single('cps_mdl_setting');
        
        //获取佣金比例数组
        $profitRate = unserialize($mdlSetting->getValueByKey('profitRate'));
        $this->pagedata['pRateType'] = $profitRate['type'];

        //设置页面显示数据
        $this->pagedata['pRate'] = $profitRate['type'] == 'whole' ? intval($profitRate['value']) : 0;
        //获取分类佣金比例
        $catRate = $mdlSetting->getValueByKey('categoryRate');
        $stedCatRate = $profitRate['type'] == 'cat' ? $profitRate['value'] : '';
        //获取现有一级分类
        $nowCat_tmp = app::get('b2c')->model('goods_cat')->getList('cat_id,cat_name',array('parent_id'=>'0'));
        //整合现有一级分类和已设置比例的一级分类
        $nowCat = array();
        foreach($nowCat_tmp as $k=>$v){
            $nowCat[$v['cat_id']]['cat_name'] = $v['cat_name'];
            $nowCat[$v['cat_id']]['cRate'] = $stedCatRate[$v['cat_id']] ? $stedCatRate[$v['cat_id']] : 0;
        }
        
        //cookie有效期
        $cookiePeriod = $mdlSetting->getValueByKey('cookiePeriod');
        
        //设置页面显示数据
        $this->pagedata['catRate'] = $nowCat;
        $this->pagedata['cookiePeriod'] = $cookiePeriod ? $cookiePeriod : 15;

        //获取佣金结算日
        $settlementDate = $mdlSetting->getValueByKey('settlementDate');

        //设置页面显示数据
        $this->pagedata['settlementDate'] = empty($settlementDate) ? '5' : $settlementDate;

        //获取订单佣金有效周期
        $orderProfitDate = $mdlSetting->getValueByKey('orderProfitDate');

        //设置页面显示数据
        $this->pagedata['orderProfitDate'] = empty($orderProfitDate) ? '15' : $orderProfitDate;

        //输出页面
        $this->page('admin/setting_rate.html', $this->app->app_id);
    }

    /**
     * 保存基础配置
     * 保存佣金比例，Cookie有效期，佣金结算日期
     * @access public
     * @version 2 Aug 5, 2011
     */
    public function save() {
        //CPS配置模型
        $mdlSetting = kernel::single('cps_mdl_setting');
        
        //佣金比例
        $profitRate = array();
        //保存佣金比例
        $profitRate['type'] = trim($_POST['prateType']);
        if($profitRate['type'] == 'whole'){
            $profitRate['value'] = intval(trim($_POST['prate'])); 
            if ($profitRate['value'] < 1 || $profitRate['value'] > 100) {
                $this->splash('error', 'index.php?app=cps&ctl=admin_setting&act=index', '佣金比例数值为1~100的整数');
            }
        }else if($profitRate['type'] == 'cat'){
            foreach($_POST['catRate'] as $k=>$v){
                $_POST['catRate'][$k] = intval(trim($v));
                if($_POST['catRate'][$k] < 1 || $_POST['catRate'][$k] > 100) {
                    $this->splash('error', 'index.php?app=cps&ctl=admin_setting&act=index', '佣金比例数值为1~100的整数');
                }
            }
            $profitRate['value'] = $_POST['catRate'];
        }
        $rs = $mdlSetting->setValueByKey('profitRate', serialize($profitRate));

        //cookie有效期
        $cookiePeriod = trim($_POST['cookiePeriod']);
        //cookie有效期有更改则进行保存
        if ($cookiePeriod != $_POST['prevCookiePeriod']) {
            //Cookie有效期为15～30天
            if ($cookiePeriod < 15 || $cookiePeriod > 30) {
                $this->splash('error', 'index.php?app=cps&ctl=admin_setting&act=index', 'Cookie有效时间为15~30天');
            }
            //保存cookie有效期
            $rs = $rs && $mdlSetting->setValueByKey('cookiePeriod', $cookiePeriod);
        }

        //订单佣金有效周期
        $orderProfitDate = trim($_POST['orderProfitDate']);
        //订单佣金有效周期有更改则进行保存
        if ($orderProfitDate != $_POST['prevOrderProfitDate']) {
            //订单佣金有效周期为7～20天
            if ($orderProfitDate < 7 || $orderProfitDate > 20) {
                $this->splash('error', 'index.php?app=cps&ctl=admin_setting&act=index', '订单佣金有效时间为7~20天');
            }
            //保存订单佣金有效周期
            $rs = $rs && $mdlSetting->setValueByKey('orderProfitDate', $orderProfitDate);
        }

        //保存佣金结算日
        $settlementDate = intval(trim($_POST['settlementDate']));
        if($settlementDate < 1 || $settlementDate > 28) {
            $this->splash('error', 'index.php?app=cps&ctl=admin_setting&act=index', '佣金结算日为1~28的整数');
        }
        $pDateRs = $mdlSetting->setValueByKey('settlementDate', $settlementDate);
        
        //根据保存结果输出信息
        if ($rs && $pDateRs) {
            $status = 'success';
            $msg = '保存成功';
        } else {
            $status = 'error';
            $msg = '保存失败';
        }

        //页面跳转
        $this->splash($status, 'index.php?app=cps&ctl=admin_setting&act=index', $msg);
    }
}