<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class stats_ctl_site_openstats extends site_controller {
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
	public function index() {
		// 保存生意经开通状态为 true
		app::get('stats')->setConf('site.stats.is_open', true);
		$this->page('site/openstats.html', true);
	}
}