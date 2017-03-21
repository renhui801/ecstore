<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class archive_finder_archive{

    // 季度开始时间
    public function time_from($time_from){
        return strtotime(sprintf('%s 00:00:00', $time_from));
    }

    // 季度结束时间
    public function time_to($time_to){
        return strtotime(sprintf('%s 23:59:59', date('Y-m-t', strtotime($time_to))));
    }


}
