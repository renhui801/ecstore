<?php
/**
 * cps_ctl_admin_info_detail
 *
 * @uses cps_admin_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_info_detail Jun 20, 2011  4:48:25 PM ever $
 */
class cps_ctl_admin_info_detail extends cps_admin_controller {

    public $workground = 'cps_center';

    /**
     * 添加消息
     * @access public
     * @version 1 Jun 24, 2011 创建
     */
    public function add() {
        //文章模型
        $mdlInfo = $this->app->model('info');
        //获取文章类型
        $infoTypes = $mdlInfo->getInfoType();

        //设置显示文章类型
        $this->pagedata['infoTypes'] = $infoTypes;
        //输出显示页面
        $this->page('admin/info_add.html', $this->app->app_id);
    }

    /**
     * 编辑消息
     * @access public
     * @version 1 Jun 24, 2011 创建
     */
    public function edit() {
        //文章模型
        $mdlInfo = $this->app->model('info');
        //获取文章类型
        $infoTypes = $mdlInfo->getInfoType();
        //获取GET文章id
        $infoId = $this->_request->get_get('infoId');
        //通过文章id获取文章内容
        $info = $mdlInfo->getInfoById($infoId);

        //设置显示数据
        $this->pagedata['info'] = $info;
        //设置显示文章类型
        $this->pagedata['infoTypes'] = $infoTypes;
        //输出显示页面
        $this->page('admin/info_edit.html', $this->app->app_id);
    }

    /**
     * 保存消息
     * @access public
     * @version 1 Jun 24, 2011 创建
     */
    public function save() {
        //文章模型
        $mdlInfo = $this->app->model('info');
        //开启事务
        $this->begin();

        $dtime = $this->_request->get_post('_DTIME_');
        $tmp_pubtime = $this->_request->get_post('pubtime') . ' ' . $dtime['H']['pubtime'] . ':' . $dtime['M']['pubtime'];
        $tmp_pubtime = strtotime($tmp_pubtime);

        $info = array(
            'title' => trim($this->_request->get_post('title')),
            'content' => trim($this->_request->get_post('content')),
            'i_type' => $this->_request->get_post('i_type'),
            'pubtime' => $tmp_pubtime,
            'ifpub' => $this->_request->get_post('ifpub'),
        );

        //标题不能为空
        if (!($info['title'])) {
            $this->end(false, '标题为空');
            return false;
        }

        //内容不能为空
        if (!($info['content'])) {
            $this->end(false, '内容不能为空');
            return false;
        }

        //定义結果变量
        $rs = true;
        //定义提示信息
        $msg = '';
        //进行插入或者更新
        if ($_GET['infoId']) {
            //更新数据結果
            $rs = $mdlInfo->update($info, array('info_id' => $_GET['infoId']));

            //根据結果设置提示信息
            if ($rs) {
                $msg = '修改成功';
            } else {
                $msg = '修改失败';
            }
        } else {
            //添加数据結果
            $rs = $mdlInfo->insert($info);

            //根据結果设置提示信息
            if ($rs) {
                $msg = '添加成功';
            } else {
                $msg = '添加失败';
            }
        }

        $this->end($rs, $msg, 'index.php?app=cps&ctl=admin_info&act=index&p[0]=' . $info['i_type']);
    }
}