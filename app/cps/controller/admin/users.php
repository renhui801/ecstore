<?php
/**
 * cps_ctl_admin_users
 * 网站联盟商控制层类
 *
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_users Jun 20, 2011  3:09:50 PM ever $
 */
class cps_ctl_admin_users extends desktop_controller {

    public $workground = 'cps_center';

    /**
     * 初始化构造方法
     * @param object $app
     * @access public
     * @version Jun 21, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 修改密码函数
     * @access public
     * @version 2 Jun 21, 2011 实现功能
     */
    public function chkpassword() {
        if ($_POST) {
            //用户
            $arrUser = array();

            //设置初始值
            $flg = 1;
            //检查密码长度
            $passwdlen = strlen($_POST['passwd']);
            if($passwdlen < 4){
                $msg = $this->app->_('密码长度不能小于4');
                $flg = 0;
            }

            if($passwdlen > 20){
                $msg = $this->app->_('密码长度不能大于20');
                $flg = 0;
            }

            //检查两次输入密码一致
            if($_POST['passwd'] != $_POST['passwd_confirm']){
                $msg = $this->app->_('输入的密码不一致');
                $flg = 0;
            }

            //验证失败
            if ($flg == 0) {
                $this->splash('error', null, $msg);
                return false;
            }

            //设置的密码
            $arrUser['passwd'] = md5(trim($_POST['passwd']));

            //开启事务
            $this->begin();
            //更新结果
            $rsPam = app::get('pam')->model('account')->update(array('login_password' => $arrUser['passwd']), array('account_id' => $_POST['u_id']));

            //根据结果设置信息
            if ($rsPam) {
                $rs = true;
                $msg = '密码修改成功';
                $url = 'index.php?app=cps&ctl=admin_users&act=index';
            } else {
                $rs = false;
                $msg = '密码修改失败';
            }

            //页面跳转
            $this->end($rs, $msg, $url);
        } else {
            //设置显示用户id
            $this->pagedata['data']['u_id'] = $_GET['uid'];
            $this->page('admin/user_passwd.html', $this->app->app_id);
        }
    }

    /**
     * 联盟商列表展示
     * @access public
     * @version 1 Jun 21, 2011 创建
     */
    public function index() {
        //列表页面参数
        $params = array(
            'title'=>$this->app->_('联盟商列表'),
            'actions'=>array(
                array('label'=>$this->app->_('添加联盟商'),
                    'href'=>'index.php?app=cps&ctl=admin_users&act=add',
                ),
                array('label'=>$this->app->_('通过'),
                    'confirm' => $this->app->_('确定通过所选联盟商？'),
                    'submit'=>'index.php?app=cps&ctl=admin_users&act=toConfirm&p[0]=1',
                ),
                array('label'=>$this->app->_('拒绝'),
                    'confirm' => $this->app->_('确定拒绝所选联盟商？'),
                    'submit'=>'index.php?app=cps&ctl=admin_users&act=toConfirm&p[0]=2',
                ),
            ),
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => true,
            'use_buildin_export' => true,
            'use_buildin_import' => false,
            'use_buildin_filter' => true,
            'use_buildin_setcol' => true,
            'use_buildin_refresh' => true,
            'use_buildin_selectrow' => true,
            'use_buildin_tagedit' => false,
            'use_view_tab' => true,
            'allow_detail_popup' => false,
        );

        $this->finder('cps_mdl_users', $params);
    }

    /**
     * 添加联盟商
     * @access public
     * @version 2 Jul 5, 2011
     */
    public function add() {
        //联盟商模型
        $mdlUser = $this->app->model('users');
        //网站模型
        $mdlWeb = $this->app->model('userweb');

        //添加联盟商
        if ($_POST) {
            //联盟商信息
            $user = $_POST['user'];
            //网站信息
            $web = $_POST['web'];
            //账户信息
            $account = $_POST['account'];

            //账户模型
            $mdlAcc = $this->app->model('userpayaccount');

            //开启事务
            $this->begin();
            //校验收款帐户信息
            $validFlag = $mdlUser->validate_account($account, $msg);
            //校验联盟商信息
            $validFlag = $mdlUser->validate($user, $msg);

            //通过校验进行保存
            if ($validFlag) {
                //释放确认密码
                unset($user['passwd_confirm']);
                //用户名转为小写
                $user['u_name'] = strtolower(trim($user['u_name']));
                //获取注册ip
                $user['reg_ip'] = base_request::get_remote_addr();
                //注册时间
                $user['regtime'] = time();
                //密码md5加密
                $user['passwd'] = md5($user['password']);
                //添加union_id
                $user['union_id'] = $mdlUser->genUnionId();

                $pam = array(
                    'account_type' => 'cpsuser',
                    'login_name' => $user['u_name'],
                    'login_password' => $user['passwd'],
                    'createtime' => $user['regtime'],
                );
                //pam新增
                $pamId = app::get('pam')->model('account')->insert($pam);

                unset($user['passwd']);
                $user['u_id'] = $pamId;
                
                //获取联盟商审核配置
                $chk = $this->app->model('setting')->getValueByKey('userCheck');
                //开启审核则为未审核状态
                if ($chk == 'true') {
                    $user['state'] = '0';
                }
                
                //保存联盟商信息
                $userId = $mdlUser->insert($user);

                $web['u_id'] = $userId;
                //保存网站信息
                $webRs = $mdlWeb->save($web);
                $account['u_id'] = $userId;
                //保存账户信息
                $accRs = $mdlAcc->save($account);

                //操作结果
                if ($pamId && $userId && $webRs && $accRs) {
                    $rs = true;
                    $msg = '添加成功';
                    $url = 'index.php?app=cps&ctl=admin_users&act=index';
                } else {
                    $rs = false;
                    $msg = '添加失败';
                    $url = 'index.php?app=cps&ctl=admin_users&act=add';
                }
            } else { //未通过校验
                $rs = false;
                $url = 'index.php?app=cps&ctl=admin_users&act=add';
            }

            //结束事务
            $this->end($rs, $msg, $url);
        } else {
            //银行模型
            $mdlBank = $this->app->model('bank');
            //获取用户类型
            $userTypes = $mdlUser->getUserTypes();
            //所有银行
            $banks = $mdlBank->getBankList(array('is_use' => 'true'));
            //设置显示用户类型
            $this->pagedata['data'] = $userTypes;
            //设置显示银行列表
            $this->pagedata['banks'] = $banks;
            //设置显示网站类型
            $this->pagedata['webTypes'] = $mdlWeb->getWebType();
            //输出添加页面
            $this->page('admin/user_add.html', $this->app->app_id);
        }
    }

    /**
     * 编辑联盟商
     * @access public
     * @version 1 Jun 28, 2011 创建
     */
    public function editUser() {
        //联盟商信息
        $user = $_POST['user'];
        //联盟商模型
        $mdlUser = $this->app->model('users');
        //修改结果
        $rs = $mdlUser->update($user, array('u_id' => $_POST['u_id']));
        $state = $rs? 'success' : 'error';
        $msg = $rs? '修改成功' : '修改失败';
        $this->splash($state, 'index.php?app=cps&ctl=admin_users&act=index', $msg);
    }

    /**
     * 编辑网站
     * @access public
     * @version 1 Jun 28, 2011 创建
     */
    public function editWeb() {
        //网站信息
        $web = $_POST['web'];
        //网站模型
        $mdlWeb = $this->app->model('userweb');
        
        //获取web_id
        if ($_POST['web_id']) {
            $web['web_id'] = $_POST['web_id'];
        }
        
        //修改结果
        $rs = $mdlWeb->save($web);
        $state = $rs? 'success' : 'error';
        $msg = $rs? '修改成功' : '修改失败';
        $this->splash($state, 'index.php?app=cps&ctl=admin_users&act=index', $msg);
    }

    /**
     * 编辑账户
     * @access public
     * @version 1 Jun 28, 2011 创建
     */
    public function editAccount() {
        //账户信息
        $acc = $_POST['account'];
        //账户模型
        $mdlWeb = $this->app->model('userpayaccount');
        
        //获取u_id
        if ($_POST['u_id']) {
            $acc['u_id'] = $_POST['u_id'];
        }
        
        //修改结果
        $rs = $mdlWeb->save($acc);
        $state = $rs? 'success' : 'error';
        $msg = $rs? '修改成功' : '修改失败';
        $this->splash($state, 'index.php?app=cps&ctl=admin_users&act=index', $msg);
    }
    
    /**
     * 审核联盟商
     * @access public
     * @param int $uid
     * @param int $state
     * @version 1 Aug 3, 2011
     */
    public function toConfirm($state, $uid) {
        //接收批量uid
        $uids = $_POST['u_id'];
        //接收单个uid
        $uids[] = $uid;
        
        //用户模型
        $mdlUser = $this->app->model('users');
        //获取用户
        $users = $mdlUser->getList('u_id, state', array('u_id' => $uids));

        //需要审核用户
        $chkUids = array();
        foreach ($users as $row) {
            if ($row['state'] == '0') {
                $chkUids[] = $row['u_id'];
            }
        }
        
        //用户未审核进行审核
        if ($chkUids) {
            $chk = array(
                'state' => $state,
            );
            //保存审核状态
            $rs = $mdlUser->update($chk, array('u_id' => $chkUids));
            $state = $rs ? 'success' : 'error';
            $msg = $rs ? '审核成功' : '审核失败';
            $this->splash($state, 'index.php?app=cps&ctl=admin_users&act=index', $msg);
        } else {
            $this->splash('error', 'index.php?app=cps&ctl=admin_users&act=index', '所选用户已审核');
        }
    }
    
    /**
     * 注册审核配置
     * @access public
     * @version 1 Aug 3, 2011
     */
    public function setting() {
        //CPS配置对象
        $mdlSetting = $this->app->model('setting');
        if ($_POST['check']) {
            //保存注册审核配置
            $rs = $mdlSetting->setValueByKey('userCheck', $_POST['check']);
            $state = $rs ? 'success' : 'error';
            $msg = $rs ? '设置成功' : '设置失败';
            $this->splash($state, 'index.php?app=cps&ctl=admin_users&act=setting', $msg);
        } else {
            //获取注册审核配置
            $check = $mdlSetting->getValueByKey('userCheck');
            //没有获取到则为false
            $check = $check ? $check : 'false';
            //设置页面变量
            $this->pagedata['chk'] = $check;
            $this->page('admin/user_setting.html', $this->app->app_id);
        }
    }
}