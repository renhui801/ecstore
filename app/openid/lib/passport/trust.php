<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class openid_passport_trust extends openid_interface_passport{

    function __construct(){
       parent::__construct();
       $this->name = '信任登录';
    }

    function get_name(){
        return null;
    }

    function get_login_form($auth, $appid, $view, $ext_pagedata=array()){
        return null;
    }

    function login($auth,&$usrdata){
        $userPassport = kernel::single('b2c_user_passport');
        if( $userPassport->userObject->is_login() )
        {
            $url = array('app'=>'b2c','ctl'=>'site_member','act'=>'index');
            kernel::single('site_controller')->splash('success',$url,app::get('b2c')->_('您已经是登录状态，不需要重新登录'));
        }

        $result = kernel::single('openid_denglu')->get_user();
        if($result['rsp'] == 'succ'){
            $login_name = $this->save_login_data($result);
        }else{
            //提示会是参数错误
            $usrdata['log_data'] = $result['err_msg'];
            $usrdata['login_name'] = false;
        }

        if(!$login_name){
            $usrdata['log_data'] = app::get('b2c')->_('保存失败，请重试');
            $usrdata['login_name'] = false;
        }else{
          $usrdata['login_name'] = $login_name;
        }
        
        return $usrdata['login_name'];
    }


    function loginout($auth,$backurl="index.php"){
        unset($_SESSION['account'][$this->type]);
        unset($_SESSION['last_error']);
    }

    function save_login_data($result,&$msg){
        $saveData['b2c_members'] = $this->pre_b2c_members_data($result);
        $saveData['pam_account'] = $this->pre_pam_members_data($result);

        $row = app::get('pam')->model('auth')->getList('auth_id,module_uid',array('module_uid'=>$saveData['pam_account']['login_account']));
        $account = app::get('pam')->model('members')->getList('member_id',array('login_account'=>$saveData['pam_account']['login_account']));
        if($row && $account){//已有信息不用再次保存
          return $saveData['pam_account']['login_account'];
        }

        $member_model = app::get('b2c')->model('members');
        $db = kernel::database();
        $db->beginTransaction();
        //保存到b2c members
        if( $member_model->insert($saveData['b2c_members']) ){
            $member_id = $saveData['b2c_members']['member_id'];
            if(!(kernel::single('b2c_user_passport')->save_attr($member_id,$saveData['b2c_members'],$msg))){
                $db->rollBack();
                return false;
            }

            $saveData['pam_account']['member_id'] = $member_id;
            if(!app::get('pam')->model('members')->save($saveData['pam_account'])){
                $db->rollBack();
                return false;
            }

            $authData = array(
              'account_id'=>$member_id,
              'module_uid'=>$saveData['pam_account']['login_account'],
              'module'=>'openid_passport_trust',
            );
            if($row[0]['auth_id']){
              $authData['auth_id'] = $row[0]['auth_id'];
            }
            if( !app::get('pam')->model('auth')->save($authData) ){
                $db->rollBack();
                return false;
            }
            
            $openidData = $this->pre_openid_data($member_id,$result);
            if( !app::get('openid')->model('openid')->save($openidData) ){
                $db->rollBack();
                return false;
            }
        }else{
            return false;
        }
        $db->commit();
        //增加会员同步 2012-05-15
        if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
            $member_rpc_object->modifyActive($member_id);
		}
		foreach(kernel::servicelist('b2c_register_after') as $object) {
			$object->registerActive($member_id);                                                                                                                   
		}
        return $saveData['pam_account']['login_account'];
    }

    public function pre_b2c_members_data($result){
        $lv_model = app::get('b2c')->model('member_lv');
        $member_lv_id = $lv_model->get_default_lv();
        $data['member_lv_id'] = $member_lv_id;
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        $data['currency'] = $arrDefCurrency['cur_code'];
        $data['email'] = $result['data']['email'];
        $data['name'] = empty($result['data']['nickname']) ? $result['data']['realname'] : $result['data']['nickname'];
        $data['addr'] = $result['data']['address'];
        $data['sex'] = $this->gender($result['data']['gender']);
        $data['trust_name'] = empty($result['data']['nickname'])?$result['data']['realname']:$result['data']['nickname'];
        return $data;
    }

    public function pre_pam_members_data($result){
        $data = $result['data'];
        if(empty($data['nickname'])){
          $login_name = $data['provider_code'].'_'.$data['realname'].'_'.$data['openid'];
        }else{
          $login_name = $data['provider_code'].'_'.$data['nickname'].'_'.$data['openid'];
        }

        $return = array(
            'login_type' => 'local',
            'login_account' => $login_name,
            'login_password' => md5(time().$login_name),
            'password_account' => $login_name, //登录密码加密账号
            'disabled' =>  'false',
            'createtime' => time() 
        );
        return $return;
    }

    public function pre_openid_data($member_id,$result){
      $save= array(
          'member_id' => $member_id,
          'openid' => $result['data']['openid'],
          'provider_code' => (string)$result['data']['provider_code'],
          'provider_openid' => (string)$result['data']['provider_openid'],
          'avatar' => $result['data']['avatar'],
          'email' => $result['data']['email'],
          'address' => $result['data']['address'],
          'gender' => $result['data']['gender'],
          'nickname' => $result['data']['nickname'],
          'realname' => $result['data']['realname'],
      );
      return $save;
    }

   function gender($gender){
        if($gender == '0'){
            return '2';
        }elseif($gender == '2'){
            return '0';
        }else{
            return $gender;
        }
    }

    

    function get_data(){
    }

    function get_id(){
    }

    function get_expired(){
    }

    /**
	* 得到配置信息
	* @return  array 配置信息数组
	*/
    function get_config(){
        $ret = app::get('pam')->getConf('passport.'.__CLASS__);
        if($ret && isset($ret['shopadmin_passport_status']['value']) && isset($ret['site_passport_status']['value'])){
            return $ret;
        }else{
            $ret = $this->get_setting();
            $ret['passport_id']['value'] = __CLASS__;
            $ret['passport_name']['value'] = $this->name;
            $ret['shopadmin_passport_status']['value'] = 'true';
            $ret['site_passport_status']['value'] = 'false';
            $ret['passport_version']['value'] = '1.5';
            app::get('pam')->setConf('passport.'.__CLASS__,$ret);
            return $ret;
        }
    }
    /**
	* 设置配置信息
	* @param array $config 配置信息数组
	* @return  bool 配置信息设置成功与否
	*/
    function set_config(&$config){
        if($config['appid']){
            $appid = $config['appid'];
            app::get('openid')->setConf('appid',$appid);
        }
        if($config['appkey']){
            $appkey = $config['appkey'];
            app::get('openid')->setConf('appkey',$appkey);
        }
        $save = app::get('pam')->getConf('passport.'.__CLASS__);
        if(count($config))
            foreach($config as $key=>$value){
                if(!in_array($key,array_keys($save))) continue;
                $save[$key]['value'] = $value;
            }
        if(empty($appid) || empty($appkey) ){
            $res = kernel::single('openid_denglu')->add();
            if($res['rsp'] == 'fail'){
                $config['error'] =  '开启失败：'.$res['err_msg'];
                return false;
            }
        }
        $save['shopadmin_passport_status']['value'] = 'false';
        return app::get('pam')->setConf('passport.'.__CLASS__,$save);
    }
   /**
	* 获取finder上编辑时显示的表单信息
	* @return array 配置信息需要填入的项
	*/
    function get_setting(){
        return array(
            'passport_id'=>array('label'=>app::get('pam')->_('通行证id'),'type'=>'text','editable'=>false),
            'passport_name'=>array('label'=>app::get('pam')->_('通行证'),'type'=>'text','editable'=>false),
            'shopadmin_passport_status'=>array('label'=>app::get('pam')->_('后台开启'),'type'=>'bool','editable'=>false),
            'site_passport_status'=>array('label'=>app::get('pam')->_('前台开启'),'type'=>'bool'),
            'passport_version'=>array('label'=>app::get('pam')->_('版本'),'type'=>'text','editable'=>false),
        );
    }



}

