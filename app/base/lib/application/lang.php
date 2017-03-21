<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class base_application_lang extends base_application_prototype_filepath 
{
    var $path = 'lang';
    var $path_ispublic = true;

    public function install() 
    {
        $dir = $this->getPathname();
        $dir = str_replace('\\', '/', $dir);
        $app_lang_dir = str_replace('\\', '/', $this->target_app->lang_dir);
        $lang_name = basename($dir);
        foreach(utils::tree($dir) AS $k=>$v){
            if(!is_file($v))  continue;
            $tree[$lang_name][] = str_replace($app_lang_dir.'/'.$lang_name.'/', '', $v);
        }
        logger::info($this->target_app->app_id . ' "' . $lang_name . '" language resource stored');
        lang::set_res($this->target_app->app_id, $tree);
    }//End Function
    
    public function clear_by_app($app_id){
        if(!$app_id){
            return false;
        }
        lang::del_res($app_id);
    }
    
}//End Class
