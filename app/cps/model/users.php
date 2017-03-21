<?php
/**
 * cps_mdl_users
 * 网站联盟商模型
 *
 * @uses dbeav_model
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_mdl_users Jun 20, 2011  2:52:01 PM ever $
 */
class cps_mdl_users extends dbeav_model {

    public $defaultOrder = 'u_id DESC';

    var $has_parent = array(
        'pam_account' => 'account@pam'
    );
    /**
     * 初始化构造
     * @access public
     * @param object $app
     * @version Jun 21, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 检查注册账号是否可用
     * @access public
     * @param string $uname 账号
     * @return int
     * @version Jun 21, 2011 创建
     */
    public function check_uname($uname) {
        //根据账号获取u_id
        $arrUser = app::get('pam')->model('account')->dump(array('login_name' => $uname, 'account_type' => 'cpsuser'), 'account_id');
        return $arrUser['account_id'];
    }

    /**
     * 注册信息验证
     * @access public
     * @param array $data 验证数据
     * @param string &$msg 提示信息
     * @return int
     * @version Jun 22, 2011 创建
     */
    public function validate($data,&$msg) {
        //标识
        $flg = 1;
        //验证注册密码
        $flg = $this->validate_password($data,$msg,$flg);
        //检查邮箱是否占用
        if(($this->is_exists_email($data['email']))){
            $msg = $this->app->_('该邮箱已经存在');
            $flg = 0;
        }
        //检查邮件格式
        if(!preg_match('/\S+@\S+/',$data['email'])){
            $msg = $this->app->_('邮件格式不正确');
            $flg = 0;
        }

        if(!$data['mobile']){
            $msg = $this->app->_('联系电话不能为空');
            $flg = 0;
        }

        //检查用户姓名非法字符
        if(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $data['realname'])){
            $msg = $this->app->_('用户姓名含非法字符');
            $flg = 0;
        }      
        if(!$data['realname']){
            $msg = $this->app->_('用户姓名不能为空');
            $flg = 0;
        }

        //检查用户名是否存在
        if($this->check_uname($data['u_name'])){
            $msg = $this->app->_('该用户名已经存在');
            $flg = 0;
        }
        //检查用户名非法字符
        if(!preg_match('/^([@\.]|[^\x00-\x2f^\x3a-\x40]){2,20}$/i', $data['u_name'])){
            $msg = $this->app->_('帐号含非法字符');
            $flg = 0;
        }
        //检查账号长度
        $unamelen = strlen($data['u_name']);
        if($unamelen < 5 || $unamelen>15){
            $msg = $this->app->_('帐号长度5~15位');
            $flg = 0;
        }
        return $flg;
    }

    
    /**
     * 注册信息验证
     * @access public
     * @param array $data 验证数据
     * @param string &$msg 提示信息
     * @return int
     * @version Jun 22, 2011 创建
     */
    public function validate_account($data,&$msg) {
        if(!$data['account']){
            $msg = $this->app->_('银行账号不能为空');
            return 0;
        }
        if(!$data['acc_bname']){
            $msg = $this->app->_('开户支行不能为空');
            return 0;
        }
        if(!$data['acc_person']){
            $msg = $this->app->_('开户人姓名不能为空');
            return 0;
        }
        return 1;
    }

    /**
     * 修改密码 注册密码验证
     * @access public
     * @param array $data 验证数据
     * @param string &$msg 提示信息
     * $param int $flg 标识
     * @return int
     * @version Jun 22, 2011 创建
     */
    public function validate_password($data,&$msg,$flg=1) {

        //检查密码长度
        $passwdlen = strlen($data['password']);
        if($passwdlen<6){
            $msg = $this->app->_('密码长度需6~16位');
            $flg = 0;
        }

        if($passwdlen>16){
            $msg = $this->app->_('密码长度需6~16位');
            $flg = 0;
        }

        //检查两次输入密码一致
        if($data['password'] != $data['passwd_confirm']){
            $msg = $this->app->_('输入的密码不一致');
            $flg = 0;
        }
        return $flg;

    }


    /**
     * 查询邮箱是否在数据中存在记录
     * @access public
     * @param string $email 邮箱
     * @return int
     * @version Jun 21, 2011 创建
     */
    public function is_exists_email($email) {
        //根据邮箱获取u_id
        $arrUser = $this->dump(array('email' => $email), 'u_id');
        return $arrUser['u_id'];
    }

    /**
     * 根据用户id获取具体用户信息
     * @access public
     * @param int $uid 联盟商id
     * @param array $aField 要获取的字段
     * @return array
     * @version 2 Jun 22, 2011 修改参数
     */
    public function getUserById($uid, $aField = array('*')) {
        //组装需要获取的字段
        $strCols = implode(',', $aField);
        //根据用户id获取用户信息
        $arrUser = $this->dump($uid, $strCols);
        return $arrUser;
    }

    /**
     * 获取所有用户类型
     * @access public
     * @return array
     * @version 1 Jun 23, 2011 创建
     */
    public function getUserTypes() {
        //所有用户类型
        $arrUserTypes = $this->schema['columns']['u_type']['type'];
        return $arrUserTypes;
    }

    /**
     * 生成加密数据
     * @access public
     * @param int $userId 联盟商id
     * @return string
     * @version 1 Jun 29, 2011 创建
     */
    public function genSecretStr($userId){
        //获取联盟商信息
        $row=$this->dump($userId);
        //md5加密用户名和密码
        $row['uname'] = md5($row['u_name']);
        $row['passwd'] = md5($row['passwd'].STORE_KEY);

        //返回加密数据
        return $userId.'-'.utf8_encode($row['uname']).'-'.$row['passwd'].'-'.time();
    }

    /**
     * fireEvent 触发事件
     *
     * @param mixed $event
     * @access public
     * @return void
     */
    function fireEvent($action , &$object, $member_id=0){
        $trigger = app::get('b2c')->model('trigger');
        return $trigger->object_fire_event($action,$object, $member_id,$this);
    }

    /**
     * 生成unionId
     * @access public
     * @return int
     * @version 1 Jul 12, 2011
     */
    public function genUnionId() {
        //生成6位1～9的字符串，然后在用户表中查询是否存在，若存在重新生成
        do {
            $strUnionId = '';
            for ($i = 0; $i < 6; $i ++) {
                $strUnionId .= rand(1, 9);
            }
        } while ($this->dump(array('union_id' => $strUnionId), 'u_id'));

        return $strUnionId;
    }
}
