<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


interface archive_interface_archivemodel{

    /**
     * 用于归档搜索，需要的额外条件
     * @return array [description]
     */
    public function extra_search_info();

    /**
     * 单据号解析成时间
     * @return string 返回时间戳
     */
    public function document2time($document_id);
}

