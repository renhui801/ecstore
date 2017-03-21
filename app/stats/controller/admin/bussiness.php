<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class stats_ctl_admin_bussiness extends desktop_controller
{
	public $workground = 'b2c_ctl_admin_sale';
	
	public $certi_id = "";
	
	public $token = "";
	
	/**
     * 췽
     * @params object app object
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
		$this->certi_id = base_certificate::certi_id();
        $this->token = base_certificate::token();
    }
	
	public function index()
	{        
        if (!$this->token){
            $this->begin('index.php?app=desktop&ctl=default&act=workground&wg=b2c.wrokground.sale');
			$this->end(false, app::get('stats')->_('LICENSE错误！'));
        }
		
        $sign = md5($this->certi_id.$this->token);
        $shoex_stat_webUrl = SHOPEX_STAT_WEBURL."?site_id=".$this->certi_id."&sign=".$sign."&innerdesktop=true";
		
        $this->pagedata['shoex_stat_webUrl'] = $shoex_stat_webUrl;
        
        $this->pagedata['certi_id'] = base_certificate::certi_id();
        $this->pagedata['sign'] = md5($this->pagedata['certi_id'].base_certificate::token());
        $this->pagedata['stats_url'] = SHOPEX_STAT_WEBURL;
        $this->pagedata['callback_url'] = urlencode( 'http://' . base_request::get_host() . app::get('site')->router()->gen_url(array('app'=>'stats','ctl'=>'site_openstats','act'=>'index')) );

        $is_open = $this->app->getConf( 'site.stats.is_open' );
        if ( $is_open ) {
            // 显示生意经统计页面
            $this->page('admin/bussiness/index.html');
        }
        else {
            // 显示激活页面
            $this->page('admin/bussiness/activation.html');
        }
        
	}
}