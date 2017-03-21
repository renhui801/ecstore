<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class dev_command_doc extends base_shell_prototype{

    var $command_update = '执行测试用例';
    var $command_update_options = array(
                                        'prefix'=>array('title'=>'项目模板','need_value'=>1),
        );
    
    function command_update(){
    	$args = func_get_args();
    	$options = $this->get_options();
    	if(!$options['prefix']){
    	    echo 'Error: need "--prefix {PATH}", define document root';
    	    return;
    	}
    	foreach($args as $app_id){
    	    kernel::single('dev_docbuilder_app')->export($app_id,$options['prefix'].'/'.$app_id);
    	}
    }

    var $command_dd = '生成数据词典';
    var $command_dd_options = array(
                                    'result-file' => array('title' => '输出文件', 'need_value' => 1, 'short' => 'f')
                                        );
    function command_dd(){
    	$args = func_get_args();
        $options = $this->get_options();
        
		$dd = kernel::single('dev_docbuilder_dd');		
		if (empty($args)) {
			$dd->export();
		}else {
			foreach($args as $app_id){
				$dd->export_tables($app_id);
			}
		}
        if ($filename = $options['result-file']) {
            ob_start();
            $dd->output();
            $out = ob_get_contents();
            ob_end_clean();

            if (!is_dir(dirname($filename))) {
                throw new Exception('cannot find the '.dirname($filename).'directory');
            }elseif(is_dir($filename)){
                throw new Exception('the result-file path is a directory.');
            }
            file_put_contents($options['result-file'], $out);
            echo 'data dictionary doc export success.';
        }else{
            $dd->output();    
        }
    }
}