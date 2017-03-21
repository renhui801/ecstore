<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class ectools_mdl_archive_refunds extends archive_model{

    var $has_many = array(
        'orders'=>'archive_order_bills@ectools:contrast:refund_id^bill_id',
    );

    public function extra_search_info(){
        return array(
            'key'=>array(
                'column'=>'refund_id',
                'label'=>'退款单号',
            ),
            'time_column'=>'t_begin',
        );
    }

    public function document2time($refund_id){
        return array(
            'start' => strtotime(date('Ymd H:00:00',substr($refund_id,0,10))),
            'end' => strtotime(date('Ymd H:59:59',substr($refund_id,0,10))),
        );
    }

    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        if(!$filter)
            return parent::_filter($filter);

        if (array_key_exists('rel_id', $filter))
        {
            $obj_order_bills = $this->app->model('archive_order_bills');
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
