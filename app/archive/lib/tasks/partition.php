<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2014 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class archive_tasks_partition extends base_task_abstract implements base_interface_task{

    function exec($params=null){
        // 按季度检查分区表是否需要维护
        $partsObj = app::get('archive')->model('partition');
        $parts = $partsObj->getList('*');
        foreach($parts as $v){
            if($v['method'] == 'range'){
                $this->maintain_partition($v);
            }
        }
    }

    /**
     * 维护range分区创建及增加,需要抽象@todo
     * @param  array $p_info 分区表相关信息
     */
    function maintain_partition($p_info){
        $table_name = kernel::database()->prefix.$p_info['app'].'_'.$p_info['table'];
        $partition_info_sql = " SELECT * FROM  information_schema.PARTITIONS AS p "
                            . " WHERE p.TABLE_SCHEMA = '" . DB_NAME . "'"
                            . " AND p.TABLE_NAME = '" . $table_name . "'"
                            . " AND p.PARTITION_ORDINAL_POSITION > 0 "
                            . " ORDER BY p.PARTITION_ORDINAL_POSITION DESC "
                            . " LIMIT 0 ,1";
        $infoPartition = kernel::database()->select($partition_info_sql);
        if(!$infoPartition){
            $currentSeason      = $this->seasonTimeStamp(); //本季度最大时间戳
            $currentSeasonPname = date('Ymd',$currentSeason);
            $nextSeason         = $this->seasonTimeStamp(1); //下季度最大时间戳
            $nextSeasonPname    = date('Ymd',$nextSeason);
            $sql = $this->range_create($p_info,$table_name,$currentSeasonPname,$currentSeason); //初始安装创建本季度分区(第一个分区)
            if(kernel::database()->select($sql)){
                $sql = $this->range_update($table_name,$nextSeasonPname,$nextSeason); //初始安装时增加下一季度分区
                kernel::database()->select($sql);
            }
        }else{
            // 队列运行当前季度时创建下一季度分区
            $nextSeason = $this->seasonTimeStamp(1);
            if($infoPartition[0]['PARTITION_DESCRIPTION']>=$nextSeason){
                return false;
            }
            $pname = date('Ymd',$nextSeason);
            $sql = $this->range_update($table_name,$pname,$nextSeason);
            kernel::database()->select($sql);
        }

    }

    /**
     * 初始创建range分区
     * @param  array $p_info        分区表相关信息
     * @param  string $table_name    需要分区的表名
     * @param  string $pname         分区名
     * @param  string $lessThanValue 本次分区的less than的value值
     * @return string                分区sql
     */
    function range_create($p_info, $table_name, $pname, $lessThanValue){
        $sql = ' ALTER TABLE ' . $table_name . ' PARTITION BY ' . $p_info['method'] . '(' . $p_info['expr'] . ') ' . '( PARTITION p' . $pname . ' VALUES LESS THAN (' . $lessThanValue . '));';
        return $sql;
    }

    /**
     * 增加range分区
     * @param  string $table_name    需要分区的表名
     * @param  string $pname         分区名
     * @param  string $lessThanValue 本次分区的less than的value值
     * @return string                分区sql
     */
    function range_update($table_name, $pname, $lessThanValue){
        $sql = ' ALTER TABLE ' . $table_name . ' ADD PARTITION ' . '( PARTITION p' . $pname . ' VALUES LESS THAN (' . $lessThanValue . '));';
        return $sql;
    }

    /**
     * 创建或者更新hash分区
     * @param  array $p_info     分区表相关信息
     * @param  string $table_name 需要分区的表名
     */
    function hash($p_info, $table_name){

        $sql = ' ALTER TABLE ' . $table_name . ' PARTITION BY ' . $p_info['method'] . '(' . $p_info['expr'] . ') PARTITIONS ' . $p_info['nums'] . ';';

        kernel::database()->select($sql);
        // return $sql;
    }

    /**
     * 获取某季度最大时间戳
     * @param  integer $season 季度递增数
     * @return timestamp       返回某季度最大时间戳
     */
    function seasonTimeStamp($season = 0){
        // 当前季度为0,往后推，几个季度数乘以3个月,1个季度1*3=3个月，两个季度家2*3=6个月......
        $plusmonth = intval($season)*3;
        // 当前或三个月后的月份开始时间
        $nextSeasonAfter = mktime(0, 0, 0, date('n')+$plusmonth , 1, date('Y'));
        // 计算得出的所在年
        $year = date('Y', $nextSeasonAfter);
        // 计算得出的所在月份
        $month = date('n', $nextSeasonAfter);
        // 计算得出的月份的季度开始月份
        $startMonth = intval(($month - 1)/3)*3 + 1;
        // 计算得出的月份的季度结束月份
        $endMonth = $startMonth + 2;
        // 计算得出的季度的最大时间
        $endMonthTime = date('Y-m-t 23:59:59', strtotime("{$year}-{$endMonth}-1"));

        return strtotime($endMonthTime);

    }

}
