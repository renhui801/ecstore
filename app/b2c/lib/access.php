<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_access{
    
    function test(){
        #$data = array('订单确认小组','订单删除小组');
        echo "<input type='checkbox' value='订单确认小组' name='order1'>".app::get('b2c')->_('订单确认小组')."</input><br/>"; 
        echo "<input type='checkbox' value='订单删除小组' name='order2'>".app::get('b2c')->_('订单删除小组')."</input><br/>"; 
        echo "<input type='checkbox' value='订单创建小组' name='order3'>".app::get('b2c')->_('订单创建小组')."</input><br/>"; 
    }
}
?>


