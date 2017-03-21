<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/**
 * 缓存数据键值
 */
class b2c_service_cachemgr_globalvary{
    
    function get_varys(){
        $GLOBALS['runtime']['member_lv'] = $_COOKIE['MLV'];
        $GLOBALS['runtime']['money'] = $_COOKIE['CUR'];
        $aTmp = array(
                        'MLV' => $_COOKIE['MLV'],
                        'CUR' => $_COOKIE['CUR'],
                    );
       return $aTmp;
    }
}
