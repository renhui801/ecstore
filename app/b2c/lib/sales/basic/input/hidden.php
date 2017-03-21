<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_sales_basic_input_hidden
{
    public $type = 'hidden';
    public function create($aData, $table_info=array()) {
        return '<input type="hidden" name="'.$aData['name'].'" value="'.$aData['default'].'" />'.$aData['desc'];
    }
}
?>
