<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

interface base_interface_db
{
    public function exec($sql, $skipModifiedMark=false, $db_lnk=null);

    public function select($sql, $skipModifiedMark=false);

    public function selectrow($sql);

    public function selectlimit($sql, $limit=10, $offset=0);

    public function getRows($rs, $row=10);

    public function count($sql);

    public function quote($string);

    public function lastinsertid();

    public function affect_row();

    public function errorinfo();

    public function errorcode();

    public function beginTransaction();

    public function commit($status=true);

    public function rollBack();

}
