<?php
/**
 * widget_user
 * CPS前台联盟商登录挂件
 *
 * @uses
 * @package CPS
 * @param array $setting
 * @param object &$smarty
 * @author zhaojingna<zhaojingna@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_site_user Jul 7, 2011  15:13:21 PM ever $
 */
function widget_user($setting,&$smarty)
{
    //开启session
    kernel::single('base_session')->start();
    //联盟商id
    $userId = $_SESSION['account']['cpsuser'];
    //联盟商模型
    $mdlUser = app::get('cps')->model('users');
    //联盟商信息
    $user = $mdlUser->dump($userId);
    return $user;
}
?>
