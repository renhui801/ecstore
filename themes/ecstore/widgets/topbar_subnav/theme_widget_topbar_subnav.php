<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_topbar_subnav($setting,&$smarty){
    $member_id = $_SESSION['account'][app::get('site')->getConf('account.type')];
    $member = app::get('b2c')->model('members');
    $member_data = $member->dump($member_id,'*',array(':account@pam'=>array('login_name')));
    $member_data['valideCode'] = app::get('b2c')->getConf('site.login_valide');
    $member_data['res_url'] = app::get('b2c')->res_url;
    if(app::get('openid')->is_actived())
    {    
        $member_data['open_id_open'] = 'true';
        $member_data['res_url'] = app::get('openid')->res_url;
    }
    else
    {
        $member_data['open_id_open'] = 'false';
    }
    
    $obj_currency = app::get('ectools')->model('currency');
    $member_data['def_cur'] = $obj_currency->getDefault();
    $member_data['base_url'] = kernel::base_url().'/';
    $member_data['cur'] = json_encode( app::get('ectools')->model('currency')->curAll() );
    return $member_data;
}
?>
