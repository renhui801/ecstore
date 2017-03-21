<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class ectools_mdl_refunds extends dbeav_model{
    
    var $has_many = array(
        'orders'=>'order_bills@ectools:contrast:refund_id^bill_id',
    );

    var $defaultOrder = array('t_payed','DESC');

    function gen_id(){
        $i = rand(0,9999);
        do{
            if(9999==$i){
                $i=0;
            }
            $i++;
            $refund_id = time().str_pad($i,4,'0',STR_PAD_LEFT);
            $row = $this->dump($refund_id, 'refund_id');
        }while($row);
        return $refund_id;
    }

    /**
     * 模板统一保存的方法
     * @params array - 需要保存的支付信息
     * @params boolean - 是否需要强制保存
     * @return boolean - 保存的成功与否的进程
     */
    public function save(&$data, $mustUpdate = NULL, $mustInsert = false)
    {
        // 异常处理    
        if (!isset($data) || !$data || !is_array($data))
        {
            trigger_error(app::get('ectools')->_("支付单信息不能为空！"), E_USER_ERROR);exit;
        }
        
        $sdf = array();
       
        // 支付数据列表
        $background = true;//后台 todo

        $payment_data = $data;
        $sdf_payment = parent::dump($data['refund_id'],'*');

        if ($sdf_payment) 
        {
            if($sdf_payment['status'] == $data['status']
                || ($sdf_payment['status'] != 'progress' && $sdf_payment['status'] != 'ready')){
                return true;
            }    
            if($data['currency'] && $sdf_payment['currency'] != $data['currency']){
                return false;
            }
        }

        if($sdf_payment){
            $sdf = array_merge($sdf_payment, $data);
        }else{
            $sdf = $data;
            //$sdf['status'] = 'ready';
        }
        // 保存支付信息（可能是退款信息）
        $is_succ = parent::save($sdf);
        
        return $is_succ;
    }
	
	/**
     * 得到所有的支付账号
     * @param null
     * @return null
     */
    public function getAccount()
    {
        $query = 'SELECT DISTINCT bank, account FROM ' . $this->table_name(1) .' WHERE status="succ"';
        return $this->db->select($query);
    }
    
    /**
     * 重写搜索的下拉选项方法
     * @param null
     * @return null
     */
    public function searchOptions(){
        $columns = array();
        foreach($this->_columns() as $k=>$v){
            if(isset($v['searchtype']) && $v['searchtype']){
                $columns[$k] = $v['label'];
            }
        }
        
        // 添加额外的
        $ext_columns = array('rel_id'=>$this->app->_('订单号'));
        
        return array_merge($columns, $ext_columns);
    }
	
	public function _filter($filter,$tableAlias=null,$baseWhere=null){
		if(!$filter)
			return parent::_filter($filter);

		if (array_key_exists('rel_id', $filter))
		{
			$obj_order_bills = $this->app->model('order_bills');
			$bill_filter = array(
				'rel_id|has'=>$filter['rel_id'],
				'bill_type'=>'refunds',
			);
			$row_order_bills = $obj_order_bills->getList('bill_id',$bill_filter);
			$arr_member_id = array();
			if ($row_order_bills)
			{
				$arr_order_bills = array();
				foreach ($row_order_bills as $arr)
				{
					$arr_order_bills[] = $arr['bill_id'];
				}
				$filter['refund_id|in'] = $arr_order_bills;				
			}
			else
			{
				$filter['refund_id'] = 'a';
			}
			unset($filter['rel_id']);
		}

        $filter = parent::_filter($filter);
        return $filter;
    }
       
    /**
     * delete 方法重载
     *
     * 根据条件删除条目
     * 不可以由pipe控制
     * 可以广播事件
     * 
     * @param mixed $filter 
     * @param mixed $named_action 
     * @access public
     * @return void
     */
    public function delete($filter, $subSdf = 'delete')
    {
        return parent::delete($filter);
    }
	
	/**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_member_id($row)
    {
        if (is_null($row) || empty($row))
        {
            return app::get('ectools')->_('未知会员或非会员');
        }

        $login_name =  kernel::single('b2c_user_object')->get_member_name(null,$row); 
        if($login_name){
            return $login_name; 
        }else{
            return app::get('ectools')->_('未知会员或非会员');
        }
    }
	
	/**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_op_id($row)
    {
        if (is_null($row) || empty($row))
        {
            return app::get('ectools')->_('未知操作员');
        }

        $login_name =  kernel::single('b2c_user_object')->get_member_name(null,$row); 
        if ($login_name){
            return $login_name;
        }
        else{
            $obj_pam_account = app::get('pam')->model('account');
            $arr_pam_account = $obj_pam_account->getList('login_name', array('account_id' => $row));
            return $arr_pam_account [0]['login_name']? $arr_pam_account [0]['login_name']: app::get('ectools')->_('未知操作员');
        }
    }
	
	/**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_pay_app_id($row)
    {
        $obj_payment_cfgs = $this->app->model('payment_cfgs');
		$arr_payment_cfgs = $obj_payment_cfgs->getPaymentInfo($row);
		
		if ($arr_payment_cfgs)
		{
			return $arr_payment_cfgs['app_name'];
		}
		else
			return 'app_name';
    }
	
	/** 
	 * 退款货币值
	 */
	public function modifier_cur_money($row)
    {
        $currency = $this->app->model('currency');
		$filter = array('refund_id' => $this->pkvalue);
        $tmp = $this->getList('currency', $filter);	
		$arr_cur = $currency->getcur($tmp[0]['currency']);
		$row = $currency->formatNumber($row,false,false);
		
		return $arr_cur['cur_sign'] . $row;
    }
	
	/**
	 * 退款收款人帐号
	 */
	public function modifier_account($row)
	{
		if (is_null($row) || empty($row))
        {
            return app::get('ectools')->_('未知收款人');
        }
		
		return $row;
	}
}
