<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class proregister_promotion_getadvance
{
    
    function __construct( &$app )
    {
        $this->app = $app;
    }
    
    public function promotion( $member_id,$money ) {
    	$app = app::get('b2c');
        $message = '注册送预存款';
        $app->model('member_advance')->add($member_id,$money,$message,$errMsg);
    }
}