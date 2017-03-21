<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_nav($setting, &$smarty){

    define('IN_SHOP',true);
    //当前的链接地址
    $cur_url = $_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];

    $result = app::get('site')->model('menus')->select()->where('hidden = ?', 'false')->order('display_order ASC')->instance()->fetch_all();

    //菜单链接
    foreach($result as $k => &$v){
        if(!empty($v['custom_url'])){
            $v['url'] = $v['custom_url'];
        }else{
            $v['url'] = kernel::router()->gen_url(
            array(
                'app'=>$v['app'],
                'ctl'=>$v['ctl'],
                'act'=>$v['act'],
                'args'=>$v['params'],
                'full'=>1
            ));
        }
        //菜单高亮
        if(stripos($v['url'], $cur_url) && 'site_ctl_default' != get_class($smarty)){
            $v['hilight'] = true;
        }
        //首页菜单高亮
        if('site_ctl_default' == get_class($smarty) && $v['app']=='site' && $v['ctl']=='default'){
            $v['hilight'] = true;
        }


    }

    $setting['max_leng'] = $setting['max_leng'] ? $setting['max_leng'] : 7;
    $setting['showinfo'] = $setting['showinfo'] ? $setting['showinfo'] : app::get('b2c')->_("更多");

    return $result;
}
?>
