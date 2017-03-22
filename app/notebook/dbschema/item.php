<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/21 0021
 * Time: 下午 5:12
 */
$db['item']=array(
    'columns' =>
        array (
            'item_id' =>  array (
                'type' => 'number',
                'required' => true,
                'extra' => 'auto_increment',
                'pkey' => true
            ),
            'item_subject' => array ( 'type' => 'varchar(100)' ),
            'item_content' => array ( 'type' => 'text' ),
            'item_posttime' => array ( 'type' => 'time' ),
            'item_email' => array ( 'type' => 'email'),
        ),
);