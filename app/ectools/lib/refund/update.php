<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * 退款单更新相关操作
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.refund
 */
class ectools_refund_update
{
    /**
     * 共有构造方法
     * @params app object
     * @return null
     */
    public function __construct($app)
    {        
        $this->app = $app;
    }
    
    /**
     * 退款当标准数据修改
     * @params array - 订单数据
     * @params string - 唯一标识
     * @return boolean - 成功与否
     */
    public function generate(&$sdf)
    {
        // 退款单修改是和中心的交互
        $objRefunds = $this->app->model('refunds');
        $data['refund_id'] = $sdf['refund_id'];
        $data['trade_no'] = $sdf['trade_no'];
        $data['t_payed'] = $sdf['t_payed'];
        $data['status'] = ($sdf['status'] == 'succ' || $sdf['status'] === true) ? 'succ' : 'failed';
        
        $filter = array(
            'refund_id' => $sdf['refund_id'],
            'status|noequal' => 'succ',
            'status|noequal' => 'progress',
        );
        
        $is_save = $objRefunds->update($data, $filter);
        
        if ($is_save)
        {
            if ($objRefunds->db->affect_row())
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