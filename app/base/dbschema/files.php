<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['files']=array (
  'columns' => 
  array (
    'file_id' => array('type'=>'number','pkey'=>true,'extra' => 'auto_increment','comment' => app::get('base')->_('文件ID'),),
    'file_path' => array('type'=>'varchar(255)','comment' => app::get('base')->_('文件路径'),),
    'file_type' =>array('type'=>array('private'=>app::get('base')->_('私有'),'public'=>app::get('base')->_('共有')),'default'=>'public', 'comment' => app::get('base')->_('文件类型'),),
    'last_change_time' => array('type'=>'last_modify', 'comment' => app::get('base')->_('最后更改时间'),),
  ),
  'version' => '$Rev: 41137 $',
  'comment' => app::get('base')->_('上传文件存放表, 非图片'),
);
