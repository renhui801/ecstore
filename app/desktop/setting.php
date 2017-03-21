<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$setting = array(
'banner'=>array('type'=>SET_T_STR,'default'=>'ECOS System'),

'format.date'=>array('type'=>SET_T_STR,'default'=>'Y-m-d','desc'=>app::get('desktop')->_('日期格式')),
'format.time'=>array('type'=>SET_T_STR,'default'=>'Y-m-d H:i:s','desc'=>app::get('desktop')->_('日期时间格式')),

'finder.thead.default.width' =>array('type'=>SET_T_STR,'default'=>'105','desc'=>app::get('desktop')->_('finder默认表头的宽度')),

);
