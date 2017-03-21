<?php
/**
 * cps_frontpage
 * CPS前台基本控制器
 *
 * @uses site_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_frontpage Jun 22, 2011  2:20:22 PM ever $
 */
class cps_frontpage extends site_controller {

    /**
     * cookie路径
     * @access private
     * @var string
     */
    private $cookiePath = '';

    /**
     * cookie生命周期
     * @access private
     * @var int
     */
    private $cookieLife = '';

    /**
     * 构造方法
     * @access public
     * @param object $app
     * @version 1 Jun 22, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 验证联盟商存在
     * @access public
     * @return true | redirect
     * @version 1 Jun 29, 2011 创建
     */
    public function verifyUser(){
        //开启session
        kernel::single('base_session')->start();

        //判断session联盟商id存在
        if ($this->app->cpsUserId = $_SESSION['account']['cpsuser']) {
            //联盟商对象
            $mdlUser = $this->app->model('users');
            //获取联盟商信息
            $data = $mdlUser->dump($this->app->cpsUserId);

            //联盟商存在，返回true
            if ($data['state'] == '1') {
                return true;
            }elseif($data['state'] == '0' || $data['state'] == '2'){
                $this->unsetUser();
                $this->redirect(array('app'=>'cps', 'ctl'=>'site_user', 'act'=>'warning', 'args'=>array($data['state'])));
            }else { //联盟商不存在，跳转回登录页面
                $this->redirect(array('app'=>'cps', 'ctl'=>'site_user', 'act'=>'login', 'arg0' => base64_encode($this->_request->get_request_uri())));
            }
        } else { //session联盟商id不存在，跳转登录页面
            $this->redirect(array('app'=>'cps', 'ctl'=>'site_user', 'act'=>'login', 'arg0' => base64_encode($this->_request->get_request_uri())));
        }

    }

    /**
     * 绑定联盟商
     * @access public
     * @param int $userId 联盟商id
     * @version 1 Jun 29, 2011 创建
     */
    public function bindUser($userId){
        //联盟商模型
        $mdlUser = $this->app->model('users');
        //获取联盟商信息
        $data = $mdlUser->dump($userId);
        
        //生成加密字符串
        $secstr = $mdlUser->genSecretStr($userId);

        //设置cookie值
        $this->setCookie('cps[user]', $secstr, NULL);
        $this->setCookie('cps[uid]', $userId, NULL);
        $this->setCookie('cps[uname]', $data['u_name'], NULL);
        $this->setCookie('cps[realname]', $data['realname'], NULL);
        $this->setCookie('cps[utype]', $data['u_type'], NULL);
    }

    /**
     * 移除联盟商
     * @access public
     * @version 1 Jul 7, 2011
     */
    public function unsetUser() {
        //开启session
        kernel::single('base_session')->start();
        
        //移除session
        unset($_SESSION['account']['cpsuser']);
        unset($_SESSION['last_error']);

        //移除联盟商id
        $this->app->cpsUserId = 0;

        //移除cookie
        $this->setCookie('cps[user]', null, time() - 3600);
        $this->setCookie('cps[uid]', '', time() - 3600);
        $this->setCookie('cps[uname]', '', time() - 3600);
        $this->setCookie('cps[realname]', '', time() - 3600);
        $this->setCookie('cps[utype]', '', time() - 3600);
    }

    /**
     * 获取当前登录联盟商信息
     * @access public
     * @return array
     * @version 1 Jun 29, 2011 创建
     */
    public function getCurrentUser() {
        //开启session
        kernel::single('base_session')->start();
        //获取session中的联盟商id
        $this->app->cpsUserId = $_SESSION['account']['cpsuser'];

        //获取联盟商信息
        $user = $this->app->model('users')->getUserById($this->app->cpsUserId);

        return $user;
    }

    /**
     * 设置cookie
     * @access public
     * @param string $name 键名称
     * @param string $value 值
     * @param int $expire 生命周期
     * @param string $path 路径
     * @version 1 Jun 29, 2011 创建
     */
    public function setCookie($name, $value, $expire = 0, $path = ''){
        //设置默认cookie路径
        if (empty($this->cookiePath)) {
            $this->cookiePath = kernel::base_url().'/';
        }

        //设置默认生命周期
        if (empty($this->cookieLife)) {
            $this->cookieLife =  $this->app->getConf('system.cookie.life');
            $this->cookieLife = $this->cookieLife > 0 ? $this->cookieLife : 315360000;
        }

        //设置cookie路径和生命周期
        $expire = $expire? $expire : time() + $this->cookieLife;
        $path = $path? $path : $this->cookiePath;

        //保存cookie值
        setcookie($name, $value, $expire, $path);
        $_COOKIE[$name] = $value;
    }

    /**
     * 检查登录状态
     * @access public
     * @return boolean
     * @version 1 Jun 29, 2011 创建
     */
    public function checkLogin(){
        //开启session
        kernel::single('base_session')->start();

        //检查联盟商id是否存在session中
        if ($_SESSION['account']['cpsuser']) {
            //登录状态返回true
            return true;
        } else {
            return false;
        }
    }

    /**
     * 结果处理
     * @var string $result
     * @var string $jumpto
     * @var string $msg
     * @var boolean $show_notice
     * @var int $wait
     * @access public
     * @return void
     * @see site_controller::splash
     */
    function splash($status='success', $jumpto=null, $msg=null, $show_notice=false, $wait=0,$ajax=false){
        if($ajax){
            header("Cache-Control:no-store, no-cache, must-revalidate"); // HTTP/1.1
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// 强制查询etag
            header('Progma: no-cache');
            if($status == 'failed'){
                $status = 'error';
            }
            $default = array(
            $status=>$msg?$msg:$this->app->_('操作成功'),
                    'redirect'=>$jumpto,
            );
            $json = json_encode($default);
            if($_FILES){
                header('Content-Type: text/html; charset=utf-8');
            }else{
                header('Content-Type:text/jcmd; charset=utf-8');
            }
            echo $json;
            exit;
        }
        if(!$msg)$msg = $this->app->_("操作成功");
        $this->_succ = true;

        $this->pagedata['msg'] = $msg;

        if(!is_null($jumpto)){
            $this->pagedata['jumpto'] = (is_array($jumpto)) ? $this->gen_url($jumpto) : $jumpto;
            if($wait > 0){
                $this->pagedata['wait'] = $wait;
            }elseif($status=='success'){
                $this->pagedata['wait'] = 1;
            }else{
                $this->pagedata['wait'] = 3;
            }
        }

        if($show_notice){
            $this->pagedata['error_info'] = $this->_err;
        }

        $this->is_splash = true;

        $this->_response->set_header('Cache-Control', 'no-store, no-cache')->set_header('Content-type', $this->contentType)->send_headers();
        $this->pagedata['title'] = $status=='success'?$this->app->_('执行成功'):$this->app->_('执行失败');
        $this->set_tmpl('cps_common');
        $this->page('site/splash/'.$status.'.html');
        echo @join("\n", $this->_response->get_bodys());
        exit;
    }
}