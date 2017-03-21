<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class dev_command_project extends base_shell_prototype{
    
    var $command_create = '创建新项目';
    var $command_create_options = array(
            'template'=>array('title'=>'项目模板','short'=>'t','need_value'=>1),
        );
    function command_create($project_name){
        $options = $this->get_options();
        $options['template'] = $options['template']?$options['template']:'dev_app';
        list($template,$template_args) = explode(':',$options['template']);
        $project_prototype = kernel::single($template);
        $project_path = $project_prototype->init($project_name,$template_args);
        if($project_path){
            $project = array(
                'name'=>$project_name,
                'path'=>$project_path,
                'createtime'=>time(),
                'type'=>$options['template'],
                );
            dev_project::save($project);  
            logger::info('Write project info... ok.');
        }else{
            logger::error('error.');
        }
    }

}

