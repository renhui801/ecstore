<?php

$db['themes_file']=array (
    'columns' => array (
        'id' =>
        array(
          'type' => 'int unsigned',
          'required' => true,
          'pkey' => true,
          'extra' => 'auto_increment',
          'editable' => false,
          'comment' => app::get('site')->_('模板文件ID'),
        ),
        'filename' => 
        array (
            'type' => 'varchar(300)',
            'comment' => app::get('site')->_('文件名'),
        ),
        'filetype' => 
        array (
            'type' => 'varchar(30)',
            'comment' => app::get('site')->_('文件扩展名'),
        ),
        'fileuri' => 
        array (
            'type' => 'varchar(300)',
            'comment' => app::get('site')->_('文件路径标识,包括模板名. [theme name]:[filename]'),
        ),
        'version' => 
        array (
            'type' => 'int',
            'required' => false,
            'comment' => app::get('site')->_('版本号'),
        ),
        'theme' => 
        array (
            'type' => 'varchar(50)',
            'comment' => app::get('site')->_('模板名标识'),
        ),
       # 'is_tmpl' =>
       # array (
       #     'type' => 'bool',
       #     'required' => true,
       #     'default'=>'false',
       # ),
        'memo' => 
        array (
            'type' => 'varchar(100)',
            'comment' => app::get('site')->_('备注'),
        ),
        'content' => 
        array (
            'type' => 'text',
			'comment' => app::get('site')->_('文件内容'),
        ),
    ),
    'version' => '$Rev: 40918 $',
    'unbackup' => true,
    'comment' => app::get('site')->_('模板文件表'),
);
