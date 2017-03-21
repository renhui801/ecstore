<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* 管理员日志记录的MODEL
*/
class operatorlog_mdl_logs extends dbeav_model 
{
    /**
	* @var string 排序方式
	*/
    var $defaultOrder = 'dateline desc';

}//End Class