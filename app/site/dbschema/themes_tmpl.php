<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
$db['themes_tmpl']=array (
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
          'comment' => app::get('site')->_('页面模板ID'),
        ),
        'tmpl_type' => 
        array (
            'type' => 'varchar(20)',
            'required' => true,
            'comment' => app::get('site')->_('对应前台页面标示符'),
        ),
        'tmpl_name' => 
        array (
            'type' => 'varchar(30)',
            'required' => true,
            'comment' => app::get('site')->_('名称'),
        ),
        'tmpl_path' => 
        array (
            'type' => 'varchar(100)',
            'required' => true,
            'comment' => app::get('site')->_('页面路径'),
        ),
        // 'version' => 
        // array (
        //     'type' => 'time',
        //     'required' => true,
        // ), 
        'theme' => 
        array (
            'type' => 'varchar(20)',
            'required' => true,
            'comment' => app::get('site')->_('对应模板'),
        ),
        // 'content' => 
        // array (
        //     'type' => 'text',
        // ),
        'rel_file_id' =>
        array (
            'type' => 'int',
            'required' => true,
            'comment' => app::get('site')->_('关联模板文件表:sdb_site_themes_file'),
        ),
    ),
    'version' => '$Rev: 40918 $',
    'unbackup' => true,
    'comment' => app::get('site')->_('页面模板表'),
);
