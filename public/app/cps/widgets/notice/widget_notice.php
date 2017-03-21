<?php
/**
 * 网站联盟公告挂件
 *
 * @param array $setting
 * @param object $smarty
 * @version 1 Jul 12, 2011
 */
function widget_notice($setting, &$smarty)
{
    //文章模型
    $mdlInfo = kernel::single('cps_mdl_info');
    //获取最新公告信息
    $list = $mdlInfo->getList('info_id, title, pubtime', array('i_type' => '1', 'ifpub' => 'true', 'pubtime|lthan' => time()), 0, $setting['limit'], 'pubtime DESC');
    return $list;
}
?>
