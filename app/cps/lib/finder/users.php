<?php
/**
 * cps_finder_users
 * 后台联盟商信息管理第三方类finder控制层类
 *
 * @uses
 * @package
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_finder_users Jun 20, 2011  3:13:18 PM ever $
 */
class cps_finder_users {

    //app属性
    private $app = null;
    //render属性
    private $render = null;

    /**
     * 初始化构造方法
     * @access public
     * @param object $app
     * @version 0.1 Jun 21, 2011 创建
     */
    public function __construct($app) {
        //初始化app属性
        $this->app = $app;
        //初始化render属性
        $this->render = $this->app->render();
    }

    //用户审核状态列
    public $column_confirm = '审核状态';
    //用户审核状态列顺序
    public $column_confirm_order = 6;
    public $column_confirm_width = 80;

    /**
     * 用户审核状态显示
     * @access public
     * @param array $row 单行用户数据
     * @return string
     * @version 1 Aug 3, 2011
     */
    public function column_confirm($row) {
        $mdlUsers = $this->app->model('users');
        $arrUser = $mdlUsers->dump($row['u_id'], 'state');

        $strRtn = '';
        if ($arrUser['state'] == '0') {
            $strRtn = '<a href="index.php?app=cps&ctl=admin_users&act=toConfirm&p[0]=1&p[1]=' . $row['u_id'] . '">通过</a>&nbsp;&nbsp;
            			<a href="index.php?app=cps&ctl=admin_users&act=toConfirm&p[0]=2&p[1]=' . $row['u_id'] . '">拒绝</a>';
        } else {
            $strRtn = $mdlUsers->schema['columns']['state']['type'][$arrUser['state']];
        }

        return $strRtn;
    }

    //显示标签
    public $detail_basic = '联盟商信息';

    /**
     * 联盟商信息显示页方法
     * @access public
     * @param int $userId 联盟商id
     * @return string
     * @version 2 Jun 28, 2011 修改显示
     */
    public function detail_basic($userId) {
        //联盟商模型
        $mdlUsers = $this->app->model('users');
        //根据联盟商id获取联盟商信息
        $arrUser = $mdlUsers->getUserById($userId);
        //联盟商类型
        $arrUserTypes = $mdlUsers->getUserTypes();

        //根据联盟商id获取联盟商账户信息
        $arrPayAcc = $this->app->model('userpayaccount')->getUserPayAccountById($userId);

        //网站模型
        $mdlWeb = $this->app->model('userweb');
        //根据联盟商id获取联盟商网站信息
        $arrUserWeb = $mdlWeb->getUserWebById($userId);
        //网站类型
        $arrWebTypes = $mdlWeb->getWebType();

        //显示数据
        $data = array(
        //联盟商信息
            'u_id' => $userId,
            'u_name' => $arrUser['u_name'],
            'email' => $arrUser['contact']['email'],
            'realname' => $arrUser['realname'],
            'mobile' => $arrUser['mobile']? $arrUser['mobile'] : $arrUser['tel'],
            'regtime' => date('Y-m-d', $arrUser['regtime']),
            'u_type' => $arrUserTypes[$arrUser['u_type']],
            'addr' => $arrUser['addr'],
            'zipcode' => $arrUser['zipcode'],
        //网站信息
            'webtype' => $arrWebTypes[$arrUserWeb['webtype']],
            'weburl' => $arrUserWeb['weburl'],
        //收款账户信息
            'account' => $arrPayAcc['account'],
            'acc_bank' => $arrPayAcc['acc_bank'],
            'acc_bname' => $arrPayAcc['acc_bname'],
            'acc_person' => $arrPayAcc['acc_person'],
        );
        //赋值页面显示数据
        $this->render->pagedata['data'] = $data;
        return $this->render->fetch('admin/user/detail_basic.html', $this->app->app_id);
    }

    //显示标签
    public $detail_edit = '编辑联盟商';

    /**
     * 修改基本信息方法
     * @access public
     * @param int $userId 联盟商id
     * @return string
     * @version 3 Jul 5, 2011
     */
    public function detail_edit($userId) {
        //联盟商模型
        $mdlUser = $this->app->model('users');
        //根据联盟商id获取联盟商信息
        $arrUser = $mdlUser->getUserById($userId);
        //显示数据
        $data = array(
        //联盟商信息
            'u_id' => $userId,
            'u_type' => $arrUser['u_type'],
            'u_name' => $arrUser['u_name'],
            'email' => $arrUser['contact']['email'],
            'realname' => $arrUser['realname'],
            'mobile' => $arrUser['mobile']? $arrUser['mobile'] : $arrUser['tel'],
        );
        //赋值页面显示数据
        $this->render->pagedata['data'] = $data;
        //设置页面显示用户类型
        $this->render->pagedata['userTypes'] = $mdlUser->getUserTypes();
        return $this->render->fetch('admin/user/detail_edit.html', $this->app->app_id);
    }

    //显示标签
    public $detail_webinfo = '编辑网站';

    /**
     * 修改网站信息方法
     * @access public
     * @param int $userId 联盟商id
     * @return string
     * @version 3 Jul 5, 2011
     */
    public function detail_webinfo($userId) {
        //联盟商网站模型
        $mdlUserWeb = $this->app->model('userweb');
        //根据联盟商id获取联盟商网站信息
        $arrUserWeb = $mdlUserWeb->getUserWebById($userId);
        //获取网站类型
        $arrWebType = $mdlUserWeb->getWebType();
        //显示数据
        $data = array(
        //网站信息
            'web_id' => $arrUserWeb['web_id'],
            'webtype' => $arrUserWeb['webtype'],
            'weburl' => $arrUserWeb['weburl'],
        );
        //赋值页面显示数据
        $this->render->pagedata['data'] = $data;
        $this->render->pagedata['webTypes'] = $arrWebType;
        return $this->render->fetch('admin/user/detail_webinfo.html', $this->app->app_id);
    }

    //显示标签
    public $detail_payaccount = '编辑账户';

    /**
     * 修改收款账户方法
     * @access public
     * @param int $userId 联盟商id
     * @return string
     * @version 2 Jun 21, 2011 修改显示
     */
    public function detail_payaccount($userId) {
        //根据联盟商id获取联盟商账户信息
        $arrPayAcc = $this->app->model('userpayaccount')->getUserPayAccountById($userId);
        //获取当前用户类型
        $userInfo = $this->app->model('users')->getUserById($userId, array('u_type'));
        //显示数据
        $data = array(
        //收款账户信息
            'u_id' => $userId,
            'account' => $arrPayAcc['account'],
            'acc_bank' => $arrPayAcc['acc_bank'],
            'acc_bname' => $arrPayAcc['acc_bname'],
            'acc_person' => $arrPayAcc['acc_person'],
            'u_type'=>$userInfo['u_type'],
        );
        //赋值页面显示数据
        $this->render->pagedata['data'] = $data;
        return $this->render->fetch('admin/user/detail_payaccount.html', $this->app->app_id);
    }

    //显示标签
    public $detail_profit = '推广相关';

    /**
     * 显示用户佣金信息方法
     * @access public
     * @param int $userId 联盟商id
     * @return string
     * @version 2 Jun 21, 2011 修改显示
     */
    public function detail_profit($userId) {
        //根据联盟商id获取联盟商信息
        $arrUser = $this->app->model('users')->getUserById($userId);
        //显示数据
        $data = array(
        //联盟商信息
            'u_id' => $userId,
            'history_profit' => sprintf('%.2f', $arrUser['history_profit']),
            'profit' => sprintf('%.2f', $arrUser['profit']),
        );
        //赋值页面显示数据
        $this->render->pagedata['data'] = $data;
        return $this->render->fetch('admin/user/detail_profit.html', $this->app->app_id);
    }
}
