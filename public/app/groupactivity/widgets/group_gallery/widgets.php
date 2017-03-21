<?php
$setting['author']='zhangxin';

$setting['version']='1';

$setting['name']=app::get('groupactivity')->_('团购列表');

$setting['catalog']    = app::get('groupactivity')->_('团购相关');

$setting['description']='团购列表';


$setting['usual']    = '0';

$setting['stime']='2008-8-8';
//,product,goods:act,
//$setting['scope']=array('');

$setting['template'] = array(
                            'default.html'=>app::get('groupactivity')->_('默认')
                        );

?>
