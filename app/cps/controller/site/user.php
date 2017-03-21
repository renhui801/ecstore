<?php
/**
 * cps_ctl_site_user
 * 联盟商前台控制层类
 *
 * @uses cps_frontpage
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_site_user Jun 20, 2011  5:23:21 PM ever $
 */
class cps_ctl_site_user extends cps_frontpage {

    /**
     * 初始化构造
     * @access public
     * @param object $app
     * @version 2 Jul 7, 2011
     */
    public function __construct($app) {
        //调用父类构造
        parent::__construct($app);
        //设置页面无浏览器缓存
        $this->_response->set_header('Cache-Control', 'no-store');
        //开启session
        kernel::single('base_session')->start();
    }

    /**
     * Ajax检查注册账号是否可用
     * @access public
     * @return int
     * @version Jun 22, 2011 创建
     */
    public function checkUname() {
        //接收POST账号值
        $uname = trim($_POST['uname']);
        //根据账号获取用户u_id
        $user = $this->app->model('users')->dump(array('u_name' => $uname), 'u_id');
        return $user['u_id'];
    }

    /**
     * CPS首页
     */
    public function index() {
        $this->set_tmpl('cps_index');
        $this->page('site/index.html');
    }

    /**
     * 挂件登录验证
     * @access public
     * @version 1 Jul 7, 2011
     */
    public function verify() {
        //验证码校验
        if(!base_vcode::verify('CPSVCODE', $_POST['loginverifycode'])){
            $this->splash('failed', $this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'index')), $this->app->_('验证码错误'));
        }
        //从pam获取用户登录记录
        $rows=app::get('pam')->model('account')->getList('account_id', array('account_type'=>'cpsuser','disabled' => 'false','login_name'=>$_POST['login'],'login_password'=>pam_encrypt::get_encrypted_password($_POST['password'],pam_account::get_account_type($this->app->app_id))));

        //获取到登录记录
        if($rows){
            //存在会员账户
            $users = kernel::single('cps_mdl_users')->dump($rows[0]['account_id']);
            if ($users) {
                //已拒绝的账号不能登录
                if($users['state'] == '2'){
                    $this->unsetUser();
                    $this->splash('failed', $this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'index')), $this->app->_('申请的账号被拒绝，请查看联盟协议'));
                }
                if($users['state'] == '0'){
                    $this->unsetUser();
                    $this->redirect(array('app'=>'cps', 'ctl'=>'site_user', 'act'=>'warning', 'args'=>array($users['state'])));
                }
                $_SESSION['account']['cpsuser'] = $rows[0]['account_id'];
                $this->bindUser($rows[0]['account_id']);
                $this->splash('success', $this->gen_url(array('app'=>'cps','ctl'=>'site_welcome','act'=>'showUser')), $this->app->_('登录成功'));
            } else { //不存在会员账户
                $this->unsetUser();
                $this->splash('failed', $this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'login')), $this->app->_('登录失败'));
            }
        }else{ //未获取到登录记录
            $_SESSION['login_msg']=$this->app->_('用户名或密码错误');
            $this->splash('failed', $this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'login')), $_SESSION['login_msg']);
        }
    }

    /**
     * 联盟商注册页显示
     * @access public
     * @version 2 Jul 6, 2011
     */
    public function register() {
        //获取用户类型
        $arrUserTypes = $this->app->model('users')->getUserTypes();
        //获取网站类型
        $arrWebTypes = $this->app->model('userweb')->getWebType();

        $this->pagedata['userTypes'] = $arrUserTypes;
        $this->pagedata['webTypes'] = $arrWebTypes;
        $this->set_tmpl('cps_common');
        $this->page('site/user/register.html');
    }

    /**
     * 联盟商注册提交
     * @access public
     * @version 2 Jul 6, 2011
     */
    public function create() {
        //联盟商模型
        $mdlUser = $this->app->model('users');
        //联盟商信息
        $user = $_POST['user'];
        //联盟商网站信息
        $web = $_POST['web'];
        //失败路径
        //$backUrl = $this->gen_url(array('app' => 'cps', 'ctl' => 'site_user', 'act' => 'register'));

        //用户名非法字符检查
        if(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $user['u_name'])){
            $this->splash('failed', $backUrl, $this->app->_('用户名包含非法字符'), '', '', true);
        }

        //验证码验证
        if(!base_vcode::verify('CPSVCODE', $_POST['verifycode'])){
            $this->splash('failed', $backUrl, $this->app->_('验证码填写错误'), '', '', true);
        }

        //同意联盟协议
        if($_POST['license'] != 'agree'){
            $this->splash('failed', $backUrl, $this->app->_('同意联盟协议后才能注册'), '', '', true);
        }

        //检验数据
        if(!$mdlUser->validate($user, $msg)){
            $this->splash('failed', $backUrl, $msg, '', '', true);
        }
        unset($user['passwd_confirm']);

        //md5加密密码
        $user['passwd'] = md5($user['password']);
        //用户名转为小写
        $user['u_name'] = strtolower(trim($user['u_name']));
        //注册ip
        $user['reg_ip'] = base_request::get_remote_addr();
        //注册时间
        $user['regtime'] = time();
        //注册邮箱
        $user['email'] = htmlspecialchars(trim($user['email']));
        //添加union_id
        $user['union_id'] = $mdlUser->genUnionId();

        //开启事务
        $this->begin();

        $pam = array(
            'account_type' => 'cpsuser',
            'login_name' => $user['u_name'],
            'login_password' => $user['passwd'],
            'createtime' => $user['regtime'],
        );
        //pam新增
        $pamId = app::get('pam')->model('account')->insert($pam);

        unset($user['passwd']);
        //联盟商id与pam id关联
        $user['u_id'] = $pamId;

        //获取联盟商审核配置
        $chk = $this->app->model('setting')->getValueByKey('userCheck');
        //开启审核则为未审核状态
        if ($chk == 'true') {
            $user['state'] = '0';
        }

        //联盟商新增
        $uId = $mdlUser->insert($user);

        $web['u_id'] = $uId;
        //联盟商网站新增
        $webId = $this->app->model('userweb')->insert($web);

        //结束事务操作
        if ($uId && $webId && $pamId) {
            $_SESSION['account']['cpsuser'] = $uId;
            $this->bindUser($uId);
            $this->end(true,$this->app->_('注册成功') , $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'showUser')), '', true);
        } else {
            $this->end(false, $this->app->_('注册失败') , $this->gen_url(array('app' => 'cps', 'ctl' => 'site_user', 'act' => 'register')), '', true);
        }
    }

    /**
     * 联盟商登录验证
     * @access public
     */
    public function login() {
        $this->set_tmpl('cps_common');
        //之前访问页面路径
        $oldUrl = $this->_request->get_param(0);

        //原先访问地址存入SESSION
        if ($oldUrl) {
            $_SESSION['CPS']['LOGIN_OLD_URL'] = $oldUrl;
        }

        //实例化pam_auth
        $auth = pam_auth::instance('cpsuser');
        $auth->set_appid($this->app->app_id);

        //设置回调函数地址
        $auth->set_redirect_url(base64_encode($this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'post_login'))));
        foreach(kernel::servicelist('passport') as $k=>$passport){
            if($auth->is_module_valid($k)){
                $passport->get_login_form($auth, 'cps', 'site/user/login.html', $pagedata);
            }
        }
        $this->pagedata['oldUrl'] = $_SESSION['CPS']['LOGIN_OLD_URL'];
        $this->page('site/user/login.html');
    }

    /**
     * 验证登录
     * @param string $oldUrl
     */
    public function post_login() {
        //通过session获取登录id
        $userId = $_SESSION['account']['cpsuser'];
        if($userId){
            $mdlUser = $this->app->model('users');
            $user = $mdlUser->dump($userId);

            //如果pam表存在记录而users表不存在记录
            if(!$user){
                $this->unsetUser();
                $this->splash('failed', $this->gen_url(array('app' => 'cps', 'ctl' => 'site_user', 'act' => 'login')), $this->app->_("登录失败"));
            } else { //pam表存在记录

                if($user['state'] == '2'){
                    $this->unsetUser();
                    $this->splash('failed', $this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'index')), $this->app->_('申请的账号被拒绝，请查看联盟协议'));
                }

                $this->bindUser($_SESSION['account']['cpsuser']);

                //取出SESSION中的原先访问地址
                $oldUrl = base64_decode($_SESSION['CPS']['LOGIN_OLD_URL']);
                unset($_SESSION['CPS']['LOGIN_OLD_URL']);
                $url = $oldUrl ? $oldUrl : $this->gen_url(array('app'=>'cps', 'ctl'=>'site_user', 'act'=>'index'));
                $this->splash('success', $url, $this->app->_('登录成功'));
            }

            $this->bindUser($userId);
            $this->redirect($this->gen_url(array('app' => 'cps', 'ctl' => 'site_user', 'act' => 'index')));
            exit;
        } else { //页面过期
            $msg = $_SESSION['error'] ? $_SESSION['error'] : $this->app->_('页面已过期,操作失败!');
            unset($_SESSION['error']);
            $this->splash('failed', $this->gen_url(array('app'=>'cps', 'ctl'=>'site_user', 'act'=>'login')), $this->app->_($msg));
            exit;
        }

    }

    /**
     * 联盟商未通过审核处理
     * @access public
     * @param string $state 审核状态
     * @version 1 Aug 3, 2011
     */
    public function warning($state) {
        //设置模板
        $this->set_tmpl('cps_common');

        //联盟商已拒绝
        if ($state == '2') {
            $this->page('site/user/register_refuse.html');
        } elseif ($state == '0') { //联盟商未审核
            $this->page('site/user/register_check.html');
        }
    }

    /**
     * 联盟商登出
     * @access public
     * @version 1 Jul 6, 2011
     */
    public function logout() {
        $this->set_tmpl('cps_common');
        $this->unsetUser();
        $this->redirect(array('app'=>'cps','ctl'=>'site_user','act'=>'index'));
    }

    function getuname(){
        $user = $this->getCurrentUser();
        $uname = $user['u_name'] ? $user['u_name'] : '';
        echo $uname;
        exit;
    }

    /**
     * 生成验证码，Key为CPSVCODE
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function verifyCode() {
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key('CPSVCODE');
        $vcode->display();
    }

    /**
     * 生成验证码，Key为app_id
     * @access public
     * @version 1 Aug 12, 2011
     */
    public function vCode() {
        $vcode = kernel::single('base_vcode');
        $vcode->length(4);
        $vcode->verify_key($this->app->app_id);
        $vcode->display();
    }

    /**
     * Ajax检查邮箱是否被注册
     * @access public
     * @return bool
     * @version 1 Jun 23, 2011 创建
     */
    public function emailCheck() {
        //检查邮箱是否被注册
        $chk = $this->app->model('users')->is_exists_email(trim($_POST['email']));
        return $chk;
    }

    /**
     * 忘记密码显示页
     * @access public
     */
    public function lost() {
        $this->path[] = array('title'=>$this->app->_('忘记密码'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $url = $this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'index'));
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)]){
            $url = $this->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            $this->splash('failed',$url,app::get('b2c')->_('请先退出'));
        }
        $this->set_tmpl('cps_common');
        $this->page("site/user/lost.html");
    }

    /**
     * 忘记密码第二步验证用户名，正确的话显示邮箱输入页
     * @access public
     * @version 1 Jun 23, 2011 创建，发送邮件未实现
     */
    public function recover() {
        $this->path[] = array('title'=>$this->app->_('忘记密码'),'link'=>'a');
        $GLOBALS['runtime']['path'] = $this->path;
        $mdlUser = $this->app->model('users');
        $rows = app::get('pam')->model('account')->getList('*',array('account_type'=>'cpsuser','login_name'=>$_POST['login']));
        $userId = $rows[0]['account_id'];
        $this->pagedata['data']=$mdlUser->dump($userId);
        $this->pagedata['data']['login_name'] = $rows[0]['login_name'];
        if(empty($userId)){
            $this->splash('failed','back',$this->app->_('该用户不存在！'));
        }
        if($this->pagedata['data']['disabled'] == "true"){
            $this->splash('failed','back',$this->app->_('该用户已经放入回收站！'));
        }
        $this->set_tmpl('cps_common');
        $this->page("site/user/recover.html");
    }

    /**
     * 验证邮箱，正确发送用户密码
     * @access public
     */
    public function sendPSW() {
        $this->begin($this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'index')));
        $rows = app::get('pam')->model('account')->getList('*',array('account_type'=>'cpsuser','login_name'=>$_POST['uname']));
        $userId = $rows[0]['account_id'];
        $mdlUser = $this->app->model('users');
        $data = $mdlUser->dump($userId);
        if(($data['answer']!=$_POST['pw_answer']) || ($data['contact']['email']!=$_POST['email'])) {
            $this->end(false,$this->app->_('问题回答错误或当前账户的邮箱填写错误'),$this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'index')));
        }

        $url = $this->gen_url(array('app'=>'cps','ctl'=>'site_user','act'=>'index'));
        $sdf = app::get('pam')->model('account')->dump($userId);
        $new_password = $this->randomkeys(6);
        $sdf['login_password'] = pam_encrypt::get_encrypted_password(trim($new_password),pam_account::get_account_type($this->app->app_id));
        if($this->send_email($_POST['uname'],$data['contact']['email'],$new_password, $userId)) {
            app::get('pam')->model('account')->save($sdf);
            $this->end(true,$this->app->_('密码变更邮件已经发送到').$data['contact']['email'].$this->app->_('，请注意查收'),$url);
        } else {
            $this->end(false,$this->app->_('发送失败，请与商家联系'),$url);
        }

    }

    public function randomkeys($length){
        $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';    //字符池
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,35)};    //生成php随机数
        }
        return $key;
    }

    public function send_email($login_name,$user_email,$new_password,$userId) {
        $ret = app::get('b2c')->getConf('messenger.actions.users-lostPw');
        $ret = explode(',',$ret);
        if(!in_array('b2c_messenger_email',$ret)) return false;
        $data['uname'] = $login_name;
        $data['passwd'] = $new_password;
        $data['email'] = $user_email;
        $mdlUser = $this->app->model('users');
        return $mdlUser->fireEvent('lostPw',$data,$userId);
    }
}
