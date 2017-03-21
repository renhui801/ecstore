<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/**
 * 过滤节点的非法字符
 */
class b2c_site_filter
{
    /**
     * 构造方法
     * @param object application object
     * @return null
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function check_input($data)
    {
        $aData = $this->arrContentReplace($data);
        
        return $aData;
    }
    
    private function arrContentReplace($array)
    {
        if (is_array($array)){
            foreach($array as $key=>$v){
                $array[$key] = $this->arrContentReplace($array[$key]);
            }
        }
        else{
            $array = strip_tags($array);
            $array = utils::_filter_input($array);//过滤xss攻击
        }
        return $array;
    }
}
