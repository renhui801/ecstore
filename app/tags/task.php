<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class tags_task 
{
    public function post_install()
    {
    	logger::info('Register tag meta');
    	$obj_tags = app::get( 'desktop' )->model( 'tag' );
    	$col = array(
    	    'params' => array(
    	        'type' => 'serialize',
    	        'editable' => false,
    	    ),
    	);
    	$obj_tags->meta_register( $col );
        logger::info('Initial tags');
        kernel::single('base_initial', 'tags')->init();
       
    }
    
    function post_uninstall(){
    	logger::info('drop tag meta');
    	$obj_tags = app::get( 'desktop' )->model( 'tag' );
    	$obj_tags->meta_meta( 'params' );
    }
}
