<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class archive_model extends dbeav_model implements archive_interface_archivemodel{

    // 用于归档搜索只根据主键或者时间筛选
    public function _filter($filter,$tableAlias=null,$baseWhere=null){
        if($filter['top_extra_view']){
            $esi = $this->extra_search_info();
            if( !$filter[$esi['key']['column']] ){
                $filter[$esi['time_column'].'|bthan'] = kernel::single('archive_finder_archive')->time_from($filter['time_from']);
                $filter[$esi['time_column'].'|lthan'] = kernel::single('archive_finder_archive')->time_to($filter['time_to']);
            }else{
                if( $mintimemax = $this->document2time($filter[$esi['key']['column']]) ){
                    $filter[$esi['time_column'].'|bthan'] = $mintimemax['start'];
                    $filter[$esi['time_column'].'|lthan'] = $mintimemax['end'];
                }
            }
            unset($filter['time_from']);
            unset($filter['time_to']);
            unset($filter['top_extra_view']);
        }

        $filter = parent::_filter($filter);
        return $filter;
    }

    public function extra_search_info(){}

    public function document2time($ids){}

}
