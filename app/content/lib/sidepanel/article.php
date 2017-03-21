<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
* sidepanel页面模版
*/
class content_sidepanel_article 
{
	/**
	* 构造方法 实例化APP
	* @param object $app app实例
	*/
    function __construct($app){
        $this->app = $app;
    }
    
	/**
	* fetch 页面
	*/
    public function get_output(){
        $render = $this->app->render();
        return $render->fetch('admin/left-panel.html');
    }
}//End Class
