<?php
/**
 * 登录注册流程/逻辑处理类
 */
class b2c_user_passport
{

    public function __construct(&$app){
        $this->app = $app;
        $this->userObject = kernel::single('b2c_user_object');
        kernel::single('base_session')->start();
        $this->obj_filter = kernel::single('b2c_site_filter');
    }


    /**
     * 设置登录后需要跳转的页面地址(pc和mobile)
     *
     */
    public function set_next_page($type='pc'){
        if(!isset($_SESSION[$type.'_next_page']) && !strpos($_SERVER['HTTP_REFERER'],'passport') ){
            $_SESSION[$type.'_next_page'] = $_SERVER['HTTP_REFERER'];
        }

        if( !isset($_SESSION[$type.'_next_page']) ){
            if($type == 'pc'){
                $url = kernel::single('site_controller')->gen_url(array('app'=>'b2c','ctl'=>'site_member','act'=>'index'));
            }else{
                $url = kernel::single('wap_controller')->gen_url(array('app'=>'b2c','ctl'=>'wap_member','act'=>'index'));
            }
            $_SESSION[$type.'_next_page'] = $url; 
        }

        return true;
    }

    /*
     * 获取到登录，注册后需要跳转到额页面地址
     *
     * @params $type stirng pc | mobile
     * */
    public function get_next_page($type='pc'){
        $url = $_SESSION[$type.'_next_page'];
        unset($_SESSION[$type.'_next_page']);
        return $url; 
    }

    /**
     *检查账号是否需要激活（ 登录手机激活|邮箱安全中心激活 ）//暂时用于历史数据手机激活
     **/
    public function check_login_account($login_account,&$msg){
        $login_account = $this->obj_filter->check_input($login_account);
        if( !empty($login_account) ){
            $pam_members_model = app::get('pam')->model('members');
            $accountData = $pam_members_model->getList('member_id,login_type,disabled',array('login_account'=>$login_account));
            $accountData = $accountData[0];
            if( $accountData ){
                if($accountData['login_type'] == 'mobile' && $accountData['disabled'] == 'true' ){
                    return true; 
                }
            }else{
                $msg = $this->app->_('此账号不存在');
                return false;
            }
        }else{
            $msg = $this->app->_('参数错误');
            return false;
        }
    }//end function

    /**
     * 检查注册POST的数据
     */
    public function check_signup($data,&$msg){
        $data = $this->obj_filter->check_input($data);

        //验证码
        $valideCode = $this->app->getConf('site.register_valide');
        $login_type = $this->get_login_account_type($data['pam_account']['login_name']);
        if( $valideCode =='true' && $login_type != 'mobile'){
          if(!base_vcode::verify('LOGINVCODE',$data['signupverifycode'])){
            $msg = $this->app->_('验证码填写错误');
            return false;
          }
        }

        if($_POST['license'] != 'on'){
            $msg = $this->app->_('同意注册条款后才能注册');
            return false;
        }

        //检查注册账号合法性
        if( !$this->check_signup_account(trim($data['pam_account']['login_name']),$msg) ){
            return false;
        }

        if($login_type == 'mobile'){
            $res = kernel::single('b2c_user_vcode')->verify($data['vcode'],$data['pam_account']['login_name'],'signup'); 
            if(!$res){
              $msg = $this->app->_('短信验证错误');
              return false;
            }
        }

        //检查密码合法，是否一致
        if( !$this->check_passport($data['pam_account']['login_password'],$data['pam_account']['psw_confirm'],$msg) ){
            return false;
        }

        return true;
    }

    /**
     * 检查密码是否合法，密码是否一致(注册，找回密码，修改密码)调用
     * @params string $password  密码
     * @params string $psw_confirm 确认密码
     *
     * @return bool
     */
    public function check_passport($password,$psw_confirm,&$msg){
        $passwdlen = strlen( trim($password) );
        if($passwdlen<6){
            $msg = $this->app->_('密码长度不能小于6位');
            return false;
        }
        if($passwdlen>20){
            $msg = $this->app->_('密码长度不能大于20位');
            return false;
        }
        if($password != $psw_confirm){
            $msg = $this->app->_('输入的密码不一致');
            return false;
        }
        return true;
    }//end function

    /*
     * 获取前台登录用户类型(用户名,邮箱，手机号码)
     *
     * @params $login_account 登录账号
     * @return $account_type
     * */
    public function get_login_account_type($login_account){
        $login_type = 'local';

        if($login_account && strpos($login_account,'@')){
            $login_type = 'email';
            return $login_type;
        }

        if(preg_match("/^1[34578]{1}[0-9]{9}$/",$login_account)){
            $login_type = 'mobile';
            return $login_type;
        }

        return $login_type;
    }//end function

    /*
     * 检查注册账号合法性
     * */
    public function check_signup_account($login_name,&$msg){
        if( empty($login_name) ){
            $msg =app::get('b2c')->_('请输入用户名');
            return false;  
        } 

        //获取到注册时账号类型
        $login_type = $this->get_login_account_type($login_name);
        switch( $login_type ){
            case 'local':
                if( strlen(trim($login_name))< 4 ){
                    $msg = $this->app->_('登录账号最少4个字符');
                    return false;
                }
                elseif( strlen($login_name)>100 ){
                    $msg = $this->app->_('登录账号过长，请换一个重试');
                }

                if( is_numeric($login_name) ){
                    $msg = $this->app->_('登录账号不能全为数字');
                    return false;
                }

                if(!preg_match('/^[^\x00-\x2d^\x2f^\x3a-\x3f]+$/i', trim($login_name)) ){
                    $msg = $this->app->_('该登录账号包含非法字符');
                    return false;
                }
                $message = $this->app->_('该账号已经被占用，请换一个重试');
                break;
            case 'email':
                if(!preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i',trim($login_name)) ){
                    $msg = $this->app->_('邮件格式不正确');
                    return false;
                }
                $message = $this->app->_('该邮箱已被注册，请更换一个');
                break;
            case 'mobile':
                $message = $this->app->_('该手机号已被注册，请更换一个');
                break;
        }

        //判断账号是否存在
        if( $this->is_exists_login_name($login_name) ){
            $msg = $message;
            return false;
        }
        $msg = $login_type;
        return true;
    }//end function

    /*
     * 判断前台用户名是否存在
     * */
    public function is_exists_login_name($login_account){
        if(empty($login_account)){
            return false;
        }
        $pam_members_model = app::get('pam')->model('members');
        $flag = $pam_members_model->getList('member_id',array('login_account'=>trim($login_account)));
        return $flag ? true : false;
    }

    /**
     *组织注册需要的数据
     */
    public function pre_signup_process($data){
        if($data['pam_account']){
            $accountData = $this->pre_account_signup_process($data['pam_account']);
        }

        $lv_model = $this->app->model('member_lv');
        $arrDefCurrency = app::get('ectools')->model('currency')->getDefault();
        if(!$data['member_lv']['member_group_id']){
            $data['member_lv']['member_group_id'] = $lv_model->get_default_lv();
        }
        $data['currency'] = $arrDefCurrency['cur_code'];
        $data['reg_ip'] = base_request::get_remote_addr();
        $data['regtime'] = time();

        //--防止恶意修改
        foreach($data as $key=>$val){
            if(strpos($key,"box:") !== false){
                $aTmp = explode("box:",$key);
                $data[$aTmp[1]] = serialize($val);
            }
        }
        $arr_colunm = array('regtime','reg_ip','currency','contact','profile','member_lv');
        $attr = $this->app->model('member_attr')->getList('attr_column');
        foreach($attr as $attr_colunm){
            $colunm = $attr_colunm['attr_column'];
            $arr_colunm[] = $colunm;
        }
        foreach($data as $post_key=>$post_value){
            if( !in_array($post_key,$arr_colunm) ){
                unset($data[$post_key]);
            }
        }
        //---end
        $return = array(
            'pam_account' => $accountData,
            'b2c_members' => $data,
        );
        $obj_filter = kernel::single('b2c_site_filter');
        $return = $obj_filter->check_input($return);
        return $return;
    }

    /**
     * 注册pam_members 表数据结构
     **/
    public function pre_account_signup_process($accountData,$password_account=null){
        $login_account = strtolower($accountData['login_name']);

        $password_account = $password_account ? $password_account : $login_account;
        $use_pass_data['login_name'] = $password_account; 
        $use_pass_data['createtime'] = time();
        $login_password = pam_encrypt::get_encrypted_password(trim($accountData['login_password']),'member',$use_pass_data);

        $login_type = $this->get_login_account_type($login_account);

        $account = array(
            'login_type' => $login_type,
            'login_account' => $login_account,
            'login_password' => $login_password,
            'password_account' => $password_account, //登录密码加密账号
            'disabled' => $login_type == 'email' ? 'true' : 'false',//邮箱需要到会员中心进行验证 
            'createtime' => $use_pass_data['createtime']
        );
        return $account;
    }

    /*
     * 根据已有的账号，新建一个账号
     * */
    public function set_new_account($member_id,$login_account,&$msg){
        if(!$member_id || empty($login_account)){
            $msg = app::get('b2c')->_('bb');
            return false;
        } 
        $login_type = $this->get_login_account_type($login_account);
        $pamMembersModel = app::get('pam')->model('members');
        $pamData = $pamMembersModel->getList('login_password,password_account,createtime',array('member_id'=>$member_id)); 
        if(!$pamData){
            $msg = app::get('b2c')->_('aa');
            return false;  
        } 
        $account = array(
            'member_id' => $member_id,
            'login_type' => $login_type,
            'login_account' => $login_account,
            'login_password' => $pamData[0]['login_password'],
            'password_account' => $pamData[0]['password_account'], //登录密码加密账号
            'disabled' =>  'false',
            'createtime' => $pamData[0]['createtime']
        );

        if( $pamMembersModel->save($account) ){
          return true;
        }else{
          return false;
        }

    }

    /*
     * 修改密码
     *
     * @params $member_id int 
     * @params $data array 
     * */
    public function save_security($member_id,$data,&$msg){ 
      $pamMembersModel = app::get('pam')->model('members');
      $pamData = $pamMembersModel->getList('login_password,password_account,createtime',array('member_id'=>$member_id)); 
      $use_pass_data['login_name'] = $pamData[0]['password_account'];
      $use_pass_data['createtime'] = $pamData[0]['createtime'];
      $login_password = pam_encrypt::get_encrypted_password(trim($data['old_passwd']),'member',$use_pass_data);
      if($login_password !== $pamData[0]['login_password']){
        $msg=app::get('b2c')->_('输入的旧密码与原密码不符！');
        return false; 
      }

      if ( !$this->check_passport($data['passwd'],$data['passwd_re'],$msg) ){
          return false;
      }

      if ( $this->reset_passport($member_id,trim($data['passwd'])) ){
          $msg = app::get('b2c')->_("密码修改成功");
      }else{
          $msg=app::get('b2c')->_('密码修改失败！');
          return false;
      }

      $arr_colunms = $this->userObject->get_pam_data('*',$member_id);
      $aData['email'] = $arr_colunms['email'];
      $aData['uname'] = $arr_colunms['local'] ? $arr_colunms['local'] : $arr_colunms['mobile'];
      $aData['uname'] = $aData['uname'] ? $aData['uname'] : $arr_colunms['email'];
      $aData['passwd'] = $data['passwd'];

      //发送邮件或者短信
      $obj_account=$this->app->model('member_account');
      $obj_account->fireEvent('chgpass',$aData,$member_id);
      return true;
    }

    /*
     * 根据会员ID 修改用户密码
     **/
    public function reset_passport($member_id,$password){
      $pamMembersModel = app::get('pam')->model('members');
      $pamData = $pamMembersModel->getList('login_account,password_account,createtime',array('member_id'=>$member_id)); 
      $db = kernel::database();
      $db->beginTransaction();
      foreach($pamData as $row){
          $use_pass_data['login_name'] = $row['password_account'];
          $use_pass_data['createtime'] = $row['createtime'];
          $login_password = pam_encrypt::get_encrypted_password(trim($password),'member',$use_pass_data);
          if(!$pamMembersModel->update(array('login_password'=>$login_password),array('login_account'=>$row['login_account']))){
              $db->rollBack();
              return false;
          }
      }
      $db->commit();
      return true;
    } 

    //设置当前用户名
    public function set_local_uname($local_uname,&$msg){
      $local_uname = strtolower($local_uname);
      $member_id = $this->userObject->get_member_id();
      if(!$member_id){
          $msg = app::get('b2c')->_('页面已过期，请重新登录，到会员中心设置'); 
          return false;
      }

      $membersData = $this->userObject->get_pam_data('*',$member_id);
      if($membersData['local']){
          $msg = app::get('b2c')->_('用户名已设置，不可更改');
          return false; 
      }
      if( !$this->check_signup_account($local_uname,$msg) ){
          return false; 
      }

      if($msg != 'local'){
          $type = ($msg =='mobile') ? app::get('b2c')->_('手机号') : app::get('b2c')->_('邮箱');
          $msg = app::get('b2c')->_('用户名不能为').$type;
          return false; 
      }

      $pamMembersModel = app::get('pam')->model('members');
      $row = $pamMembersModel->getList('login_account,login_password,password_account,createtime',array('member_id'=>$member_id)); 
      $row = $row[0];

      $data['member_id'] = $member_id;
      $data['login_account'] = strtolower($local_uname);
      $data['login_type'] = 'local';
      $data['login_password'] = $row['login_password'];
      $data['password_account'] = $row['password_account'];
      $data['createtime'] = $row['createtime'];

      if( $pamMembersModel->insert($data)  ){
        $msg = app::get('b2c')->_('用户名设置成功');
        return true;
      }else{
        $msg = app::get('b2c')->_('用户名设置失败');
        return false;
      }

    }

    //获取会员注册项加载
    public function get_signup_attr($member_id=null){
        if($member_id){
            $member_model = $this->app->model('members');
            $mem = $member_model->dump($member_id);
        }
        $member_model = $this->app->model('members');
        $mem_schema = $member_model->_columns();
        $attr =array();
        foreach($this->app->model('member_attr')->getList() as $item)
        {
            if($item['attr_show'] == "true") $attr[] = $item; //筛选显示项
        }
        foreach((array)$attr as $key=>$item)
        {
            $sdfpath = $mem_schema[$item['attr_column']]['sdfpath'];
            if($sdfpath)
            {
                $a_temp = explode("/",$sdfpath);
                if(count($a_temp) > 1)
                {
                    $name = array_shift($a_temp);
                    if(count($a_temp))
                        foreach($a_temp  as $value){
                            $name .= '['.$value.']';
                        }
                }
            }
            else
            {
                $name = $item['attr_column'];
            }
            if($mem && $item['attr_group'] == 'defalut'){
                switch($attr[$key]['attr_column']){
                case 'area':
                    $attr[$key]['attr_value'] = $mem['contact']['area'];
                    break;
                case 'birthday':
                    $attr[$key]['attr_value'] = $mem['profile']['birthday'];
                    break;
                case 'name':
                    $attr[$key]['attr_value'] = $mem['contact']['name'];
                    break;
                case 'mobile':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['mobile'];
                    break;
                case 'tel':
                    $attr[$key]['attr_value'] = $mem['contact']['phone']['telephone'];
                    break;
                case 'zip':
                    $attr[$key]['attr_value'] = $mem['contact']['zipcode'];
                    break;
                case 'addr':
                    $attr[$key]['attr_value'] = $mem['contact']['addr'];
                    break;
                case 'sex':
                    $attr[$key]['attr_value'] = $mem['profile']['gender'];
                    break;
                case 'pw_answer':
                    $attr[$key]['attr_value'] = $mem['account']['pw_answer'];
                    break;
                case 'pw_question':
                    $attr[$key]['attr_value'] = $mem['account']['pw_question'];
                    break;
                }
            }

            if($item['attr_group'] == 'contact'||$item['attr_group'] == 'input'||$item['attr_group'] == 'select'){
                $attr[$key]['attr_value'] = $mem['contact'][$attr[$key]['attr_column']];
                if($item['attr_sdfpath'] == ""){
                    $attr[$key]['attr_value'] = $mem[$attr[$key]['attr_column']];
                    if($attr[$key]['attr_type'] =="checkbox"){
                        $attr[$key]['attr_value'] = unserialize($mem[$attr[$key]['attr_column']]);
                    }
                }
            }

            if($attr[$key]['attr_type'] == 'select' ||$attr[$key]['attr_type'] == 'checkbox'){
                $attr[$key]['attr_option'] = unserialize($attr[$key]['attr_option']);
            }

            $attr[$key]['attr_column'] = $name;
            if($attr[$key]['attr_column']=="birthday"){
                $attr[$key]['attr_column'] = "profile[birthday]";
            }
        }

        return $attr;
    }//end function

    /*
     *暂时未启用
     * */
    public function gen_secret_str($member_id, $login_name, $login_password){
        $login_password = md5($login_password.STORE_KEY);
        return $member_id.'-'.utf8_encode($login_name.'-'.$login_password.'-'.time());
    }

    /*
     * 保存会员信息members表和注册扩展项数据
     *
     **/
    public function save_members($saveData,&$msg){
        $saveData = $this->obj_filter->check_input($saveData);
        $member_model = $this->app->model('members');
        $db = kernel::database();
        $db->beginTransaction();
        if( $member_model->save($saveData['b2c_members']) ){
            $member_id = $saveData['b2c_members']['member_id'];
            if(!($this->save_attr($member_id,$saveData['b2c_members'],$msg))){
                $db->rollBack();
                return false;
            }

            $saveData['pam_account']['member_id'] = $member_id;
            if(!app::get('pam')->model('members')->save($saveData['pam_account'])){
                $db->rollBack();
                return false;
            }
            $db->commit();
        }else{
            $msg = '保存失败，请联系客服!';
            return false;
        }
        return $member_id;
    }

    /**
     * save_attr
     * 保存会员注册扩展信息
     *
     * @access private
     * @return bool
     */
    public function save_attr($member_id=null,$aData,&$msg)
    {
        if(!$member_id){
            $msg = app::get('b2c')->_('保存失败,请重试或联系客服');
            return false;
        }
        $member_model = $this->app->model('members');
        if($member_model->save($aData)){
            $msg = app::get('b2c')->_('保存成功');
            return true;
        }
        $msg  = app::get('b2c')->_('保存失败,请重试或联系客服');
        return false;
    }
}
                
