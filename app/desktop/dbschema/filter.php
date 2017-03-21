<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
/**
* @table magicvars;
*
* @package Schemas
* @version $
* @copyright 2003-2009 ShopEx
* @license Commercial
*/

$db['filter']=array (
  'columns' => 
  array (
    'filter_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'pkey' => true,
      'extra' => 'auto_increment',
      'editable' => false,
      'comment' => app::get('dbeav')->_('finder过滤器ID'),
    ),
    'filter_name' => 
    array (
      'type' => 'varchar(20)',
      'required' => false,
      'label' => app::get('desktop')->_('筛选器名'),
      'class' => 'span-3',
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'comment' => app::get('dbeav')->_('过滤条件名称'),
    ),
    'user_id' => 
    array (
      'type' => 'number',
      'required' => true,
      'label' => app::get('desktop')->_('用户id'),
      'width' => 110,
      'editable' => false,
      'hidden' => true,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('dbeav')->_('过滤器所属后台用户ID'),
    ),
    'model' => 
    array (
      'type' => 'varchar(100)',
      'required' => true,
      'label' => app::get('desktop')->_('表'),
      'class' => 'span-3',
      'in_list' => true,
      'default_in_list' => true,
      'editable' => false,
      'comment' => app::get('desktop')->_('过滤器对应的model(表名)'),
    ),
    'filter_query' => 
    array (
      'type' => 'text',
      'hidden' => true,
      'label' => app::get('desktop')->_('筛选条件'),
      'class' => 'span-4',
      'in_list' => true,
      'editable' => false,
      'comment' => app::get('desktop')->_('过滤器对应的过滤条件'),
    ),
    'ctl'=>array(
      'type' => 'varchar(100)',
      'required' => true,
      'default'=>'',
      'label' => app::get('desktop')->_('控制器'),
      'class' => 'span-3',
      'editable' => false,
      'comment' => app::get('desktop')->_('过滤器对应的controller(控制器)'),
    ),
    'app'=>array(
      'type' => 'varchar(50)',
      'required' => true,
      'default'=>'',
      'label' => app::get('desktop')->_('应用'),
      'class' => 'span-3',
      'editable' => false,
      'comment' => app::get('desktop')->_('过滤器对应的app(应用)'),
    ),
    'act'=>array(
      'type' => 'varchar(50)',
      'required' => true,
      'default'=>'',
      'label' => app::get('desktop')->_('方法'),
      'class' => 'span-3',
      'editable' => false,
      'comment' => app::get('desktop')->_('过滤器对应的act(方法)'),
    ),
    'create_time' => 
    array (
      'type' => 'time',
      'default' => 0,
      'required' => true,
      'label' => app::get('desktop')->_('建立时间'),
      'width' => 110,
      'editable' => false,
      'in_list' => true,
      'default_in_list' => true,
      'comment' => app::get('desktop')->_('过滤器创建时间'),
    ),
  ),
  'comment' => app::get('desktop')->_('后台搜索过滤器表'),
);