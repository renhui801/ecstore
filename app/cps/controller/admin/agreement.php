<?php
/**
 * cps_ctl_admin_agreement
 * 后台联盟协议控制层类
 *
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_agreement Jun 20, 2011  3:30:31 PM ever $
 */
class cps_ctl_admin_agreement extends desktop_controller {

    public $workground = 'cps_center';

    /**
     * 列表展示
     * @access public
     * @version 1 Jun 22, 2011 创建
     */
    public function index() {
        //协议数据
        $arrAgree = $this->app->model('agreement')->getAgreementInfo();
        //页面显示数据
        $this->pagedata['data'] = $arrAgree;
        $this->page('admin/setting_agree.html', $this->app->app_id);
    }

    /**
     * 保存联盟协议
     * @access public
     * @version 2 Jun 22, 2011 修改跳转地址
     */
    public function save() {
        //协议内容
        $arrAgree['agreement'] = $_POST['agreement'];

        $cnt = trim(strip_tags($arrAgree['agreement']));

        //协议内容不能为空
        if (empty($cnt)) {
            $this->splash('error', 'index.php?app=cps&ctl=admin_agreement&act=index', '不能输入空信息，请重新输入');
            return false;
        }

        //协议id
        if (isset($_POST['agree_id'])) {
            $arrAgree['agree_id'] = $_POST['agree_id'];
        }

        //保存结果
        $rs = $this->app->model('agreement')->save($arrAgree);

        //页面跳转
        if ($rs) {
            $this->splash('success', '', '保存成功');
        } else {
            $this->splash('error', '', '保存失败');
        }
    }
}