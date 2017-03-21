<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

 
class setting_service extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
          $this->libray = new base_syscache_setting();
    }

    public function testGetData(){

        var_dump($this->libray->get_data());
    }

    public function testGetOne(){

        
        var_dump(syscache::instance('setting')->get('setting/site-current_theme'));
    }
    
}

