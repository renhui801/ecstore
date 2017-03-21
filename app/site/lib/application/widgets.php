<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_application_widgets extends base_application_prototype_filepath 
{
    var $path = 'widgets';

    public function install() 
    {
        if(is_dir($this->getPathname())){
            $widgets_name = basename($this->getPathname());
            $widgets_app = $this->target_app->app_id;
            logger::info('Installing Widgets '. $widgets_app . ':' . $widgets_name);
            $data['app'] = $widgets_app;
            $data['name'] = $widgets_name;
            app::get('site')->model('widgets')->insert($data);
            site_widgets::set_last_modify();
        }
    }//End Function
    
    function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        site_widgets::set_last_modify();
        app::get('site')->model('widgets')->delete(array('app'=>$app_id));
    }
    
}//End Class
