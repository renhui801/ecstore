<?php
/**
 * cps_ctl_admin_bank
 * 开户银行管理控制层类
 *
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_bank Jun 20, 2011  3:36:50 PM ever $
 */
class cps_ctl_admin_bank extends desktop_controller {

    public $workground = 'cps_center';

    /**
     * 构造初始化方法
     * @param object $app
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 开户银行列表方法
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function index() {
        //列表页面参数
        $params = array(
            'title'=>$this->app->_('银行配置'),
            'actions'=>array(
                array(
                    'label'=>$this->app->_('添加联盟商银行'),
                    'icon'=>'add.gif',
                    'href'=>'index.php?app=cps&ctl=admin_bank&act=addNew',
                ),
            ),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => true,
            'use_buildin_export' => false,
            'use_buildin_import' => false,
            'use_buildin_filter' => true,
            'use_buildin_setcol' => true,
            'use_buildin_refresh' => true,
            'use_buildin_selectrow' => true,
            'use_buildin_tagedit' => false,
            'use_view_tab' => true,
            'allow_detail_popup' => false,
        );

        $this->finder('cps_mdl_bank', $params);
    }

    /**
     * 新增开户银行页方法
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function addNew() {
        if ($_POST) {
            //获取POST银行名称
            $bank['b_name'] = trim($_POST['bname']);

            if($bank['b_name'] == 0 && !empty($_POST['other'])){
                $bank['b_name'] = $_POST['other'];
            }

            //银行模型
            $mdlBank = $this->app->model('bank');
            $this->begin();
            if ($mdlBank->dump(array('b_name' => $bank['b_name']), 'b_id')) {
                $status = false;
                $url = 'index.php?app=cps&ctl=admin_bank&act=addNew';
                $msg = $bank['b_name'] . '已存在';
            } else {
                //添加银行
                $rs = $mdlBank->save($bank);
                if ($rs) {
                    $status = true;
                    $url = 'index.php?app=cps&ctl=admin_bank&act=index';
                    $msg = '添加银行成功';
                } else {
                    $status = false;
                    $url = 'index.php?app=cps&ctl=admin_bank&act=addNew';
                    $msg = '添加银行失败';
                }
            }
            $this->end($status, $msg, $url);
        } else {
            $this->page('admin/setting_bank.html', $this->app->app_id);
        }
    }

    /**
     * 编辑开户银行页方法
     * @access public
     * @version 1 Jun 23, 2011 创建，暂时不用
     * @deprecated
     */
    public function showEdit() {

    }
}