<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class stats_ctl_site_statsajax extends site_controller
{
    /**
     * 构造方法，追加此页面的头部属性
     * @param object application对象
     * @return null
     */
    public function __construct(&$app)
    {
        parent::__construct($app);
        $this->header .= '<meta name="robots" content="noindex,noarchive,nofollow" />';
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->title=app::get('stats')->_('获取数据');
    }
    
    /**
     * 外部去kvstore的接口，抛出后台订单，会员相关的数据给rpc
     * @param null
     * @return null
     */
    public function index()
    {    
        if ($_POST['method'] == 'getkvstore')
        {
            $arr_shift_out = array();
            $str_stats = $this->app->getConf('SHOPEX_STAT_ADMIN');
            
            if (isset($str_stats) && $str_stats)
            {
                $arr_stats = unserialize($str_stats);
                if (is_array($arr_stats) && $arr_stats)
                {
                    foreach ($arr_stats as $key=>&$arr_stats_info)
                    {
                        if ($arr_stats_info)
                        {
                            $arr_shift_out = array_shift($arr_stats_info);
                            break;
                        }
                    }
                }
            }
            
            $this->app->setConf('SHOPEX_STAT_ADMIN', serialize($arr_stats));
            
            echo json_encode($arr_shift_out);exit;
        }
        else
        {
            echo "";exit;
        }
    }
}
