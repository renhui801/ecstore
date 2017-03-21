<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class syscache_service extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
          $this->libray = new base_syscache_service();
    }

    public function testGetData(){

        var_dump($this->libray->get_data());
    }

}
