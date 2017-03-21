<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class site_keyboard_initdata {
    //配置数组
    public $setting = array();
    
    
    public function __construct( &$app ) {
        $o = app::get('desktop')->router();
        
        $url = $o->gen_url( array('app'=>'site','act'=>'index','ctl'=>'admin_theme_manage') );
        $setting['导航菜单上的栏目'][] = array('title'=>'打开站点主菜单','params'=>array('keyword'=>'6','control'=>array('alt'=>'true')),'arg'=>$url);
        
        $this->setting = $setting;
    }
}
