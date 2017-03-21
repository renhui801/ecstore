<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 支付单修改的具体实现逻辑
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.payment
 */
class ectools_payment_update
{
    /**
     * @var app object
     */
    public $app;

    /**
     * 构造方法
     * @param object app
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 支付单修改
     * @param array sdf
     * @param string message
     * @return boolean sucess of failure
     */
    public function generate(&$sdf, &$msg='')
    {
        // 修改支付单是和中心的交互
        $objPayments = $this->app->model('payments');
        $data['payment_id'] = $sdf['payment_id'];
        $data['trade_no'] = $sdf['trade_no'];
        $data['t_payed'] = $sdf['t_payed'];
        //$data['status'] = ($sdf['status'] == 'succ' || $sdf['status'] === true || $sdf['status'] == 'progress') ? $sdf['status'] : 'failed';
        $data['status'] = $sdf['status'];
        if(isset($sdf['thirdparty_account']) && $sdf['thirdparty_account']){
            $data['thirdparty_account'] = $sdf['thirdparty_account'];
        }

        $filter = array(
            'payment_id' => $sdf['payment_id'],
            'status|noequal' => 'succ',
        );
        $is_save = $objPayments->update($data, $filter);

        if ($is_save)
        {
            // 防止重复充值
            if ($objPayments->db->affect_row())
                return true;
            else
                return false;
        }
        else
        {
            $msg = app::get('ectools')->_('支付单修改失败！');
            return false;
        }
    }
}
