<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */ 

interface b2c_apiv_interface_requestout
{
    /**
     * init
     * 初始化
     * @params $sdf array 请求数据
     * @return void
     */
    public function init($sdf);
}