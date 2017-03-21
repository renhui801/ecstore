<?php
/**
 * cps_mdl_usermonthprofit
 * 网站联盟月度佣金模型
 *
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_usermonthprofit Jun 20, 2011  2:48:08 PM ever $
 */
class cps_mdl_usermonthprofit extends dbeav_model {

    public $defaultOrder = 'ump_id DESC';
    
    /**
     * 初始化构造方法
     * @access public
     * @param object $app
     * @version Jun 21, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 获取单个月月度佣金统计
     * @access public
     * @param array $filter
     * @return array
     * @version Jun 22, 2011 修改参数
     */
    public function getMonthProfit($filter) {
        //获取某个联盟商单个月度佣金统计信息
        $monthProfit = $this->dump($filter, 'ump_id, year, month, u_id,
            u_name, order_sum, cost_sum, money_sum, state, disabled');
        return $monthProfit;
    }
    
    /**
     * 导出需要的数据
     * @access public
     * @param array &$data
     * @param array $filter
     * @param int $offset
     * @return bool
     * @version 1 Jul 15, 2011
     */
    public function fgetlist_csv(&$data,$filter,$offset) {
		//CSV标题
		$title = array(
			'开户人姓名',
			'开户银行',
			'账户类型',
			'开户支行',
			'开户账号',
			'用户名',
			'发放状态',
			'佣金总额',
		);
		
		$data['title'] = '"' . implode('","', $title) . '"';
		
		//联盟商银行账户模型
		$mdlUpa = kernel::single('cps_mdl_userpayaccount');
		//联盟商模型
		$mdlUser = kernel::single('cps_mdl_users');
		//用户类型
		$userTypes = $mdlUser->getUserTypes();
		//发放状态
		$umpState = $this->getStates();
		
		$limit = 100;
		if (!$umps = $this->getList('u_id, u_name, state, money_sum', $filter, $offset * $limit, $limit)) {
			return false;
		}
		
		$cnt = array();
		//根据u_id获取用户信息
		foreach ($umps as $ump) {
			$upa = $mdlUpa->dump($ump['u_id'], 'acc_person, acc_bank, acc_bname, account');
			$user = $mdlUser->dump($ump['u_id'], 'u_type');
			$cnt[] = '"' . $upa['acc_person'] . '","' . $upa['acc_bank'] . '","' . $userTypes[$user['u_type']] . 
				'","' . $upa['acc_bname'] . '","' . $upa['account'] . '","' . $ump['u_name'] . '","' . 
				$umpState[$ump['state']] . '","' . $ump['money_sum'] . '"';
		}
		
		$data['contents'] = $cnt;
		return true;
	}
	
	/**
	 * 获取所有发放状态
	 * @access public
	 * @version 1 Jul 15, 2011
	 */
	public function getStates() {
		//发放状态
		$arrStates = $this->schema['columns']['state']['type'];
		return $arrStates;
	}
}