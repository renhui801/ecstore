<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

$setting['author']='shopex.cn';
$setting['name']=app::get('cps')->_('用户自定义HTML');
$setting['version']='135711';

$setting['stime']='2008-12-13';

$setting['catalog']=app::get('cps')->_('自定义版块');

$setting['usual']    = '1';

$setting['description']    = ''.app::get('cps')->_('支持所有HTML定义.').'<br><br>'.app::get('cps')->_('如需查看该版块的使用说明，请').'<a href="http://www.shopex.cn/bbs/read.php?tid-64552.html" target="_blank">'.app::get('cps')->_('点击这里').'</a>。';

//,product,goods:act,
//$setting['scope']=array('');

$setting['template'] = array(
                            'default.html'=>app::get('cps')->_('默认')
                        );

?>
