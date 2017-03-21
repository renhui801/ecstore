<?php
/**
 * cps_auto_statistics
 * 月度佣金统计自动任务
 *
 * @uses
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_auto_statistics Jul 12, 2011  3:08:57 PM ever $
 */
class cps_auto_statistics {

    //年
    private $year = 0;
    //月
    private $month = 0;
    //yam值
    private $yam = 0;

    /**
     * 构造初始，存入yam，年和月
     * @access public
     * @version 1 Jul 12, 2011
     */
    public function __construct() {
        //上个月的时间
        $tm = time() - 2419200;
        //yam值
        $this->yam = date('Ym', $tm);
        $this->year = date('Y', $tm);
        $this->month = date('m', $tm);
    }
    
    /**
     * 测试环境下，每分进行数据统计
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function minute() {}

    /**
     * 每小时进行任务
     * @access public
     * @version 1 Jul 12, 2011
     */
    public function hour() {
        $day = date('d', time());
        //获取后台设置的佣金结算日
        $settlementDate = app::get('cps')->model('setting')->getValueByKey('settlementDate');

        //每个月指定号进行自动统计月度佣金
        if ($day >= $settlementDate) {
            $this->execStatistics();
        }
    }
    
    /**
     * 每天进行任务
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function day() {}
    
    /**
     * 每周进行任务
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function week() {}
    
    /**
     * 每月进行任务
     * @access public
     * @version 1 Jul 13, 2011
     */
    public function month() {}

    /**
     * 月度佣金进行统计
     * @access public
     * @version 1 Jul 12, 2011
     */
    public function execStatistics() {
        //不存在上个月的佣金记录，进行统计
        if (!$this->isExeced()) {
            $this->genStatistics();
        }
    }

    /**
     * 检查上个月的记录是否存在
     * @access public
     * @return int
     * @version 1 Jul 12, 2011
     */
    private function isExeced() {
        //联盟商月度佣金模型
        $mdlUmp = kernel::single('cps_mdl_usermonthprofit');
        //获取上个月的月度佣金记录
        $rs = $mdlUmp->dump(array('year' => $this->year, 'month' => $this->month), 'ump_id');
        return $rs['ump_id'];
    }

    /**
     * 进行数据统计
     * @access private
     * @version 1 Jul 12, 2011
     */
    private function genStatistics() {
        //联盟商订单佣金模型
        $mdlUop = kernel::single('cps_mdl_userorderprofit');
        //联盟商月度佣金模型
        $mdlUmp = kernel::single('cps_mdl_usermonthprofit');

        //获取上个月的有效订单佣金记录
        $strSql = 'SELECT u_id, u_name, order_cost, money, yam 
        		FROM sdb_cps_userorderprofit 
        		WHERE yam = ' . $this->yam . ' AND state = \'2\'';
        $arrUop = $mdlUop->db->select($strSql);

        //统计数据
        $arrSts = array();
        foreach ((array)$arrUop as $row) {
            //联盟商的数据累计
            if ($arrSts[$row['u_id']]) {
                $arrSts[$row['u_id']]['order_sum'] ++;
                $arrSts[$row['u_id']]['cost_sum'] += $row['order_cost'];
                $arrSts[$row['u_id']]['money_sum'] += $row['money'];
            } else { //联盟商的首数据
                $arrSts[$row['u_id']]['u_id'] = $row['u_id'];
                $arrSts[$row['u_id']]['u_name'] = $row['u_name'];
                $arrSts[$row['u_id']]['year'] = $this->year;
                $arrSts[$row['u_id']]['month'] = $this->month;
                $arrSts[$row['u_id']]['state'] = '1';
                $arrSts[$row['u_id']]['disabled'] = 'false';

                $arrSts[$row['u_id']]['order_sum'] = 1;
                $arrSts[$row['u_id']]['cost_sum'] = $row['order_cost'];
                $arrSts[$row['u_id']]['money_sum'] = $row['money'];
            }
        }

        //数据库对象
        $db = kernel::database();
        //开启事务
        $db->beginTransaction();

        //批量存入月度佣金数据
        foreach ($arrSts as $row) {
            //存入月度佣金
            $mdlUmp->save($row);
            //修改用户未发放佣金
            $strSql = 'UPDATE sdb_cps_users SET profit = profit + ' . $row['money_sum'] . ' WHERE u_id = ' . $row['u_id'];
            $db->exec($strSql);
        }

        //提交事务
        $db->commit(true);
    }
}