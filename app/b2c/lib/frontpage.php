<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_frontpage extends site_controller{
    //todo
    protected $member = array();
    function __construct(&$app){
        parent::__construct($app);
    }

    /**
    * 检测用户是否登陆
    *
    * 当用户没有登陆则跳转到登陆错误页面
    *
    * @param      none
    * @return     void
    */
    function verify_member(){
        $userObject = kernel::single('b2c_user_object');
        if( $this->app->member_id = $userObject->get_member_id() ){
            $data = $userObject->get_members_data(array('members'=>'member_id'));
            if($data){
                //登录受限检测
                $res = $this->loginlimit($data['members']['member_id'],$redirect);
                if($res){
                    $this->redirect($redirect);
                }else{
                    return true;
                }
            }else{
                $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
            }
        }else{
            $this->redirect(array('app'=>'b2c', 'ctl'=>'site_passport', 'act'=>'error'));
        }

    }
    /**
    * loginlimit-登录受限检测
    *
    * @param      none
    * @return     void
    */
    function loginlimit($mid,&$redirect) {
        $services = kernel::servicelist('loginlimit.check');
        if ($services){
            foreach ($services as $service){
                $redirect = $service->checklogin($mid);
            }
        }
        return $redirect?true:false;
    }//End Function


    public function bind_member($member_id){
        $columns = array(
            'account'=> 'member_id,login_account,login_password',
            'members'=> 'member_id,member_lv_id,cur,lang',
        );
        $userObject = kernel::single('b2c_user_object');
        $cookie_expires = $userObject->cookie_expires ? time() + $userObject->cookie_expires * 60 : 0;
        $data = $userObject->get_members_data($columns);
        $secstr = kernel::single('b2c_user_passport')->gen_secret_str($member_id, $data['account']['login_name'], $data['account']['login_password']);
        $login_name = $userObject->get_member_name($data['account']['login_name']); 
        $this->cookie_path = kernel::base_url().'/';
        #$this->set_cookie('MEMBER',$secstr,0);
        $this->set_cookie('UNAME',$login_name,$cookie_expires);
        $this->set_cookie('MLV',$data['members']['member_lv_id'],$cookie_expires);
        $this->set_cookie('CUR',$data['members']['cur'],$cookie_expires);
        $this->set_cookie('LANG',$data['members']['lang'],$cookie_expires);
        $this->set_cookie('S[MEMBER]',$member_id,$cookie_expires);
    }

    public function get_current_member()
    {
        
      return kernel::single('b2c_user_object')->get_current_member();
    }

    public function _check_verify_member($member_id=0)
    {
        if (isset($member_id) && $member_id)
        {
            $userObject = kernel::single('b2c_user_object');
            $current_member_id = $userObject->get_member_id();
            if ($member_id != $current_member_id)
            {
                $this->begin();
                $this->end(false,  app::get('b2c')->_('订单无效！'), $this->gen_url(array('app'=>'site','ctl'=>'default','act'=>'index')));
            }
            else
            {
                return true;
            }
        }

        return false;
    }

    function set_cookie($name,$value,$expire=false,$path=null){
        if(!$this->cookie_path){
            $this->cookie_path = kernel::base_url().'/';
            #$this->cookie_path = substr(PHP_SELF, 0, strrpos(PHP_SELF, '/')).'/';
            $this->cookie_life =  $this->app->getConf('system.cookie.life');
        }
        $this->cookie_life = $this->cookie_life > 0 ? $this->cookie_life : 315360000;
        $expire = $expire === false ? time()+$this->cookie_life : $expire;
        setcookie($name,$value,$expire,$this->cookie_path);
        $_COOKIE[$name] = $value;
    }

    function check_login(){
        kernel::single('base_session')->start();
        if($_SESSION['account'][pam_account::get_account_type($this->app->app_id)]){
            return true;
        }
        else{
            return false;
        }
    }
    /*获取当前登录会员的会员等级*/
    function get_current_member_lv()
    {
        kernel::single('base_session')->start();
        if($member_id = $_SESSION['account'][pam_account::get_account_type($this->app->app_id)]){
           $member_lv_row = app::get("pam")->model("account")->db->selectrow("select member_lv_id from sdb_b2c_members where member_id=".intval($member_id));
           return $member_lv_row ? $member_lv_row['member_lv_id'] : -1;
        }
        else{
            return -1;
        }
    }
    function setSeo($app,$act,$args=null){
        $seo = kernel::single('site_seo_base')->get_seo_conf($app,$act,$args);
        $this->title = $seo['seo_title'];
        $this->keywords = $seo['seo_keywords'];
        $this->description = $seo['seo_content'];
        $this->nofollow = $seo['seo_nofollow'];
        $this->noindex = $seo['seo_noindex'];
    }//End Function

    function get_member_fav($member_id=null){
        $obj_member_goods = $this->app->model('member_goods');
		return $obj_member_goods->get_member_fav($member_id);
    }


}
