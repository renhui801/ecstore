<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class base_application_tips{
    
    static function tip_apps(){
        $apps = array();
        $lang = kernel::get_lang();
        if ($handle = opendir(PUBLIC_DIR.'/app')) {
            while (false !== ($file = readdir($handle))) {
                if($file{0}!='.' && is_dir(PUBLIC_DIR.'/app/'.$file) && file_exists(PUBLIC_DIR.'/app/'.$file.'/lang/'.$lang.'/tips.txt')){
                    $apps[] = $file;
                }
                if(defined('CUSTOM_CORE_DIR') && $file{0}!='.' && is_dir(CUSTOM_CORE_DIR.'/'.$file) && file_exists(CUSTOM_CORE_DIR.'/'.$file.'/lang/'.$lang.'/tips.txt')){
                    $apps[] = $file;
                }
            }
            closedir($handle);
        }
        return $apps;
    }
    
    static function tips_item_by_app($app_id){
        $lang = kernel::get_lang();
        $tips = array();
        foreach(file(PUBLIC_DIR.'/app/'.$app_id.'/lang/'.$lang.'/tips.txt')  as $tip){
            $tip = trim($tip);
            if($tip){
                $tips[] = $tip;
            }
        }
        return $tips;
    }
    
    static function tip(){

        $apps = self::tip_apps();
        $key = array_rand($apps);
        $app_id = $apps[$key];
        if(empty($app_id)) return '';
        
        $tips = self::tips_item_by_app($app_id);
        $key = array_rand($tips);
        return $tips[$key];
    }
    
}
