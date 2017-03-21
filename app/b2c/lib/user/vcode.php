<?php
/*
 * 登录/注册/找回密码 手机号发送验证码,验证码存储，验证
 * */
class b2c_user_vcode{

    var $ttl = 86400;

    public function __construct(&$app){
        $this->app = $app;
        kernel::single('base_session')->start();
    }

    //随机取6位字符数
    public function randomkeys($length){
        $key = '';
        $pattern = '1234567890';    //字符池
        for($i=0;$i<$length;$i++){
            $key .= $pattern{mt_rand(0,9)};    //生成php随机数
        }
        return $key;
    }

    /*
     * 将验证码存储到缓存中(没开启缓存则存储到kv) 短信验证码或者邮箱验证码
     *
     * @params $account 手机号码 | 邮箱
     * @params $type  signup 注册 | activation登录激活 | forgot 忘记密码 | reset 会员中心修改手机或者邮箱发送验证码
     * */
    public function set_vcode($account,$type='signup',&$msg){
        $vcodeData = $this->get_vcode($account,$type);
        if($vcodeData && !strpos($account,'@')){
            if( $vcodeData['createtime'] == date('Ymd') && $vcodeData['count'] == 6 ){
                $msg = $this->app->_('每天只能进行6次验证');
                return false;
            }

            if( time() - $vcodeData['lastmodify'] < 120 ){
               $msg = $this->app->_('2分钟发送一次,还没到两分钟则不进行发送');
               return false;
            }

            if( $vcodeData['createtime'] != date('Ymd') ){
                $vcodeData['count'] = 0;
            }
        }

        $vcode = $this->randomkeys(6);
        $vcodeData['account'] = $account;
        $vcodeData['vcode'] = $vcode;
        $vcodeData['count']  += 1;
        $vcodeData['createtime'] = date('Ymd');
        $vcodeData['lastmodify'] = time();
        $key = $this->get_vcode_key($account,$type);

        if(defined('WITHOUT_CACHE') && !constant('WITHOUT_CACHE')){
            cacheobject::set($key,$vcodeData,$this->ttl+time());
        }else{
            base_kvstore::instance('vcode/account')->store($key,$vcodeData,$this->ttl);
        }
        return $vcode;
    }


    /*
     *
     * $vcode=>array(
     *   'account' => '13918087543',
     *   'vcode' => '123456',//验证码
     *   'count' => '4',
     *   'createtime'=> date('Ymd');
     *   'lastmodify'=> time(),
     * );
     *
     * */
    public function get_vcode($account,$type='signup'){
        $key = $this->get_vcode_key($account,$type);
        if(defined('WITHOUT_CACHE') && !constant('WITHOUT_CACHE')){
            cacheobject::get($key,$vcode);
        }else{
            base_kvstore::instance('vcode/account')->fetch($key,$vcode);
        }

        return $vcode;
    }

    /*
     * 删除验证码（非物理删除，重新生成一个验证码）
     * */
    public function delete_vcode($account,$type,$vcodeData){
        $vcode = $this->randomkeys(6);
        $vcodeData['vcode'] = $vcode;
        $key = $this->get_vcode_key($account,$type);
        if(defined('WITHOUT_CACHE') && !constant('WITHOUT_CACHE')){
            cacheobject::set($key,$vcodeData,$this->ttl+time());
        }else{
            base_kvstore::instance('vcode/account')->store($key,$vcodeData,$this->ttl);
        }
        return $vcodeData; 
    }

    public function send_sms($tmpl,$mobile,$data){
        if( !$tmpl = $this->sendtype_to_tmpl($tmpl) ) return false;
        $messengerModel = $this->app->model('member_messenger');
        $actions = $messengerModel->actions();
        $level = $actions[$tmpl]['level'];
        $sendType = $actions[$tmpl]['sendType'];

        $sender = 'b2c_messenger_sms';
        $tmpl_name = 'messenger:b2c_messenger_sms/'.$tmpl; 
        if($level < 10){ //队列
            $messengerModel->addQueue($sender,$tmpl_name,(string)$mobile,$data,$tmpl,$sendType);
        }else{ //直接发送 print
            $messengerModel->_send($sender,$tmpl_name,(string)$mobile,$data,$tmpl,$sendType);
        }
        return true;
    }

    public function send_email($tmpl,$email,$data){
        if( !$tmpl = $this->sendtype_to_tmpl($tmpl) ) return false;
        $messengerModel = $this->app->model('member_messenger');
        $actions = $messengerModel->actions();
        $level = $actions[$tmpl]['level'];
        $sendType = $actions[$tmpl]['sendType'];

        $sender = 'b2c_messenger_email';
        $tmpl_name = 'messenger:b2c_messenger_email/'.$tmpl; 
        if($level < 10){ //队列
            $messengerModel->addQueue($sender,$tmpl_name,(string)$email,$data,$tmpl,$sendType);
        }else{ //直接发送 print
            $messengerModel->_send($sender,$tmpl_name,(string)$email,$data,$tmpl,$sendType);
        }
        return true;
    }

    public function sendtype_to_tmpl($sendtype){
        $tmpl = false;
        switch($sendtype){
            case 'activation': //激活
                $tmpl = 'account-member';
                break;
            case 'reset': //重置手机号或者邮箱
                $tmpl = 'account-member';
                break;
            case 'forgot': //找回密码 
                $tmpl = 'account-lostPw';
                break;
            case 'signup': //手机注册
                $tmpl = 'account-signup';
                break;
        } 
        return $tmpl;
    }

    public function get_vcode_key($account,$type='signup'){
        return md5($account.$type);
    }

    //手机激活验证
    public function mobile_login_verify($vcode,$mobile,$type){
        if( !kernel::single('b2c_user_passport')->check_login_account($mobile) ){
            return true;
        }

        if( $this->verify($vcode,$mobile,$type) ){
            app::get('pam')->model('members')->update(array('disabled'=>'false'),array('login_account'=>$mobile)); 
        }else{
            return false;
        }
        return true;
    } 

    public function verify($vcode,$send,$type){
        if(empty($vcode) ) return false;
        $vcodeData = $this->get_vcode((string)$send,$type); 
        if($vcodeData && $vcodeData['vcode'] == $vcode){
            $data = $this->delete_vcode($vcodeData['account'],$type,$vcodeData);
            return $data; 
        }else{
            return false;
        }
    }

}
