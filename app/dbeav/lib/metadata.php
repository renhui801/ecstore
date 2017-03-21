<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
 * dbeav_meta
 * meta?
 *
 * @uses modelFactory
 * @package
 * @version $Id$
 * @copyright 2003-2007 ShopEx
 * @author Ever <ever@shopex.cn>
 * @license Commercial
 */

 class dbeav_metadata
 {
    private $_meta_columns = array();
	
	function __construct(){
		$sql = "select * from sdb_dbeav_meta_register";
		$arr_rows = kernel::database()->select($sql);
		foreach ($arr_rows as $row){
			$this->_meta_columns[$row['tbl_name']][$row['col_name']] = $row;
		}
	}
	
	public function get_all(){
		return $this->_meta_columns;
	}
 
 }