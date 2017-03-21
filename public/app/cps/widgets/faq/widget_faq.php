<?php
/**
 * 网站联盟常见问题挂件
 *
 * @param array $setting
 * @param object $smarty
 * @version 1 Jul 12, 2011
 */
function widget_faq($setting, &$smarty)
{
    //文章模型
    $mdlInfo = kernel::single('cps_mdl_info');
    //获取常见问题信息
    $list = $mdlInfo->getList('info_id, title', array('i_type' => '2', 'ifpub' => 'true'), 0, $setting['limit']);
    return $list;
}
?>
