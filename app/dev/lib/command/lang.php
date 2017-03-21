<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class dev_command_lang extends base_shell_prototype{
    
    var $command_reset = '重新加载语言包资源文件';
    function command_reset(){ 
        $rows = app::get('base')->model('apps')->getlist('*',array('installed'=>true));
        $langObj = kernel::single('base_application_lang');
        foreach($rows as $k=>$v){
            $core_dir = '';
            if( is_dir(app::get($v['app_id'])->public_app_dir.'/lang') ){
                $core_dir =  app::get($v['app_id'])->public_app_dir.'/lang';
            }
            if( $core_dir ){
                foreach($langObj->detect($v['app_id']) as $name=>$item){
                    $item->install();
                }
            }
        }
        return true;
    }
}
