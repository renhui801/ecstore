<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

$db['emailtmpl'] = array(
    'columns' => array(
        'et_name' => array(
            'type' => 'varchar(100)',
            'label' => app::get('emailsubs')->_('关键字'),
            'pkey' => true,
            'required' => true,
            'hidden' => true,
            'editable' => false,
            'in_list' => false,
        ),
        'et_content' => array(
            'type' => 'longtext',
            'label' => app::get('emailsubs')->_('邮件模板'),
            'hidden' => true,
            'editable' => false,
            'in_list' => false,
            'filtertype' => 'normal',
        ),
    ),
    'comment' => app::get('emailsubs')->_('邮件模板表'),
    'engine' => 'innodb',
    'version' => '$Rev: 44513 $',
);
