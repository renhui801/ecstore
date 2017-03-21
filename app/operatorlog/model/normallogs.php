<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* 管理员日志记录的MODEL
*/
class operatorlog_mdl_normallogs extends dbeav_model
{
    /**
    * @var string 排序方式
    */
    var $defaultOrder = 'dateline DESC';


    public function modifier_memo($row)
    {
        $this->delimiter = kernel::single('operatorlog_service_desktop_controller')->get_delimiter();

        if(substr($row,0,9) == 'serialize'){
            $memo_arr = explode($this->delimiter, $row);
            $row = $memo_arr[1];
        }
        return mb_substr($row, 0, 30, 'utf-8');
    }

}//End Class