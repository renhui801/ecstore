<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 这个类实现报表的数据统计和显示的接口
 * @interface
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.analysis
 */
interface archive_finder_interface 
{

    /**
     * 设置报表统计的参数
     * @param array 需要设置的参数
     * @return object 本类对象
     */
    public function set_params($params);
    
    /**
     * 设置extra视图
     * @param array view视图数组
     * @return object 本类对象
     */
    public function set_extra_view($array);

    /**
     * 生成各自统计内容的finder
     * @param null
     * @return array - finder统一格式的数组
     */
    public function finder();
    
    /**
     * fetch 页面的html
     * @param null
     * @return string html页面nei'ron
     */
    public function fetch();
    
    /**
    * 展示页面内容的方法
    * @param boolean true - 提出内容，相当于fetch，false echo内容
    * @return string html结果内容
    */
    public function display($fetch=false);
        
}//End Class