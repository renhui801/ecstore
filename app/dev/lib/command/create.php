<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class dev_command_create extends base_shell_prototype{
    
    var $command_app = '创建新的app';
    function command_app($app_name=null){
        if(!$app_name){
             $app_name = readline('app name: ');
        }

	do {
	    $app_path = APP_DIR.'/'.$app_name;
	    $app_path = realpath($app_path);
        } while(!$appname && (strlen($app_path) >= strlen(APP_DIR)) && file_exites($app_path ));

	$base_dir = APP_DIR.'/base';
	logger::info('Init App...'.$app_name);
        utils::cp($base_dir.'/examples/app',APP_DIR.'/'.$app_name);
        utils::replace_p(APP_DIR.'/'.$project_name,array('%*APP_NAME*%'=>$project_name));
        
        logger::info('. done!');
	
	return true;

    }


}

