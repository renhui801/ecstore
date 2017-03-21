#!/usr/bin/env php
<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$root_dir = realpath(dirname(__FILE__).'/../../');
require($root_dir.'/config/queue.php');

$list = array_keys($queues);

echo @implode(' ', $list);
