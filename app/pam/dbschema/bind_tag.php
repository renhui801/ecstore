<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
$db['bind_tag'] = array(
    'columns'=>
    array(
        'id'=>
        array(
            'type'=>'number',
            'pkey'=>true,
            'extra' => 'auto_increment',
            'comment' => app::get('pam')->_('ID'),
        ),
        'tag_type'=>
        array(
            'type'=> array(
                'weixin'=>app::get('pam')->_('微信'),
            ),
            'is_title'=>true,
            'default'=>'weixin',
            'required' => true,
            'comment' => app::get('pam')->_('绑定平台'),
        ),
        'open_id'=>
        array(
            'type'=>'varchar(100)',
            'comment' => app::get('pam')->_('绑定平台唯一ID'),
        ),
        'tag_name'=>
        array(
            'type'=>'varchar(100)',
            'comment' => app::get('pam')->_('绑定平台的昵称'),
        ),
        'member_id'=>
        array(
            'type'=>'varchar(32)',
            'required' => true,
            'comment' => app::get('pam')->_('绑定会员'),
        ),
        'disabled'=>
        array(
            'type'=>'bool',
            'default'=>'false',
        ),
        'createtime'=>
        array(
            'type'=>'time',
            'comment' => app::get('pam')->_('创建时间'),
        ),
    ),
    'index' => array (
        'open_id' => array ('columns' => array ('open_id'),'prefix' => 'UNIQUE'),
    ),
    'engine' => 'innodb',
    'comment' => app::get('pam')->_('绑定第三方平台'),
);
