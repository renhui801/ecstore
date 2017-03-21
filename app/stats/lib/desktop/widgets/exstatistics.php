<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class stats_desktop_widgets_exstatistics implements desktop_interface_widget
{   
    /**
     * 构造方法，初始化此类的某些对象
     * @param object 此应用的对象
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app; 
        $this->render =  new base_render(app::get('stats'));  
    }
    
    /**
     * 获取桌面widgets的标题
     * @param null
     * @return null
     */
    public function get_title()
    {            
        return app::get('stats')->_("生意经分析");        
    }
    
    /**
     * 获取桌面widgets的html内容
     * @param null
     * @return string html内容
     */
    public function get_html()
    {
        $render = $this->render;
        $render->pagedata['page_url'] = SHOPEX_STAT_WEBURL;
        $render->pagedata['certi_id'] = base_certificate::certi_id();
        $render->pagedata['sign'] = md5($render->pagedata['certi_id'].base_certificate::token());
        $render->pagedata['stats_url'] = 'http://stats.shopex.cn/index.php';
        $render->pagedata['callback_url'] = urlencode( 'http://' . base_request::get_host() . app::get('site')->router()->gen_url(array('app'=>'stats','ctl'=>'site_openstats','act'=>'index')) );

		$is_open = $this->app->getConf( 'site.stats.is_open' );
		if ( $is_open ) {
			// 取到生意经的授权
			return $render->fetch('desktop/widgets/exstatistics.html');
		}
        else {
        	// 显示激活页面
        	return $render->fetch('desktop/widgets/activation.html');
        }
    }
    
    /**
     * 获取页面的当前widgets的classname的名称
     * @param null
     * @return string classname
     */
    public function get_className()
    {        
        return " valigntop exstatistics";
    }
    
    /**
     * 显示的位置和宽度
     * @param null
     * @return string 宽度数据
     */
    public function get_width()
    {          
        return "l-2";        
    }
}