<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/*
 * @package base
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license 
 */

interface base_charset_interface{
    
    public function local2utf($strFrom,$charset='zh');

    public function utf2local($strFrom,$charset='zh');

    public function u2utf8($str);

    public function utf82u($str);

    public function replace_utf8bom($str);

    public function is_utf8($word);
}