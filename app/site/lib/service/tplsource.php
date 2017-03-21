<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_service_tplsource 
{

    public function last_modified($widgets_id) 
    {
        return app::get('site')->model('widgets_proinstance')->select()->columns('modified')->where('widgets_id = ?',$widgets_id)->instance()->fetch_one();
    }//End Function

    public function get_file_contents($widgets_id) 
    {
        return '<{widget id=' . $widgets_id . '}>';
    }//End Function


}//End Class
