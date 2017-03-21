<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * @description 短信接口加密类
 */
class b2c_messenger_iBase64{

    private function pattern(){
        return array(
        '+'=>'_1_',
        '/'=>'_2_',
        '='=>'_3_',
        );
    }
    public function encode($str){
        $str = base64_encode($str);
        return strtr($str, $this->pattern());
    }

    public function decode($str){
        $str = strtr($str, array_flip($this->pattern()));
        return base64_decode($str);
    }
}