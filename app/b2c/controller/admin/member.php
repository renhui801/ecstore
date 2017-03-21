<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_member extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';
    var $pagelimit = 10;
    var $member_model ;
    public function __construct($app)
    {
        parent::__construct($app);
        $this->member_model = $this->app->model('members');
        header("cache-control: no-store, no-cache, must-revalidate");
    }

   function index(){
        //增加会员相关权限判断@lujy
        if($this->has_permission('addmember')){
            $custom_actions[] = array('label'=>app::get('b2c')->_('添加会员'),'href'=>'index.php?app=b2c&ctl=admin_member&act=add_page','target'=>'dialog::{title:\''.app::get('b2c')->_('添加会员').'\',width:460,height:460}');
        }
        if($this->has_permission('send_email')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发邮件'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_email','target'=>'dialog::{title:\''.app::get('b2c')->_('群发邮件').'\',width:700,height:400}');
        }
        if($this->has_permission('send_msg')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发站内信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_msg','target'=>'dialog::{title:\''.app::get('b2c')->_('群发站内信').'\',width:500,height:350}');
        }
        if($this->has_permission('send_sms')){
            $custom_actions[] =  array('label'=>app::get('b2c')->_('群发短信'),'submit'=>'index.php?app=b2c&ctl=admin_member&act=send_sms','target'=>'dialog::{title:\''.app::get('b2c')->_('群发短信').'\',width:500,height:350}');
        }

        $actions_base['title'] = app::get('b2c')->_('会员列表');
        $actions_base['actions'] = $custom_actions;
        $actions_base['allow_detail_popup'] = true;
        $actions_base['use_buildin_set_tag'] = true;
        $actions_base['use_buildin_export'] = true;
        $actions_base['use_buildin_filter'] = true;
        $actions_base['use_view_tab'] = true;

        $this->finder('b2c_mdl_members',$actions_base);
    }

   function _views(){
        $mdl_member = $this->app->model('members');
        //今日新增会员
        $today_filter = array(
                    '_regtime_search'=>'between',
                    'regtime_from'=>date('Y-m-d'),
                    'regtime_to'=>date('Y-m-d'),
                    'regtime' => date('Y-m-d'),
                    '_DTIME_'=>
                        array(
                            'H'=>array('regtime_from'=>'00','regtime_to'=>date('H')),
                            'M'=>array('regtime_from'=>'00','regtime_to'=>date('i'))
                        )
                );
        $today_reg = $mdl_member->count($today_filter);
        $sub_menu[0] = array('label'=>app::get('b2c')->_('今日新增会员'),'optional'=>true,'filter'=>$today_filter,'addon'=>$today_reg,'href'=>'index.php?app=b2c&ctl=admin_member&act=index&view=0&view_from=dashboard');

        //昨日新增
        $date = strtotime('yesterday');
        $yesterday_filter = array(
                    '_regtime_search'=>'between',
                    'regtime_from'=>date('Y-m-d',$date),
                    'regtime_to'=>date('Y-m-d'),
                    'regtime' => date('Y-m-d',$date),
                    '_DTIME_'=>
                        array(
                            'H'=>array('regtime_from'=>'00','regtime_to'=>date('H',$date)),
                            'M'=>array('regtime_from'=>'00','regtime_to'=>date('i',$date))
                        )
                );
        $yesterday_reg = $mdl_member->count($yesterday_filter);
        $sub_menu[1] = array('label'=>app::get('b2c')->_('昨日新增会员'),'optional'=>true,'filter'=>$yesterday_filter,'addon'=>$yesterday_reg,'href'=>'index.php?app=b2c&ctl=admin_member&act=index&view=1&view_from=dashboard');

        //TAB扩展
        foreach(kernel::servicelist('desktop_member_view_extend') as $service){
            if(method_exists($service,'getViews')) {
                $service->getViews($sub_menu);
            }
        }

         foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array(),$v['filter']);
                }else{
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                if($k==$_GET['view']){
                    $show_menu[$k]['addon'] = $mdl_member->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=b2c&ctl=admin_member&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

    function add_page(){
        $member_lv=$this->app->model("member_lv");
        foreach($member_lv->getMLevel() as $row){
            $options[$row['member_lv_id']] = $row['name'];
        }
        $a_mem['lv']['options'] = is_array($options) ? $options : array(app::get('b2c')->_('请添加会员等级')) ;
        $attr = kernel::single('b2c_user_passport')->get_signup_attr();
        $this->pagedata['attr'] = $attr;
        $this->pagedata['mem'] = $a_mem;
        $this->display('admin/member/new.html');
    }

    function namecheck(){
      $userPassport = kernel::single('b2c_user_passport');
      if( !$userPassport->check_signup_account($_POST['pam_account']['login_name'],$message) ){
          echo json_encode(array('error'=>$message));exit;
      }
      echo json_encode(array('success'=>app::get('b2c')->_('该登录账号可用')));exit;
    }

    function add(){
      $this->begin();
      $userPassport = kernel::single('b2c_user_passport');
      $member_model = app::get('b2c')->model('members');
      
      if( !$userPassport->check_signup_account($_POST['pam_account']['login_name'],$message) ){
        $this->end(false, $message);
      }

      if(!$userPassport->check_passport($_POST['pam_account']['login_password'],$_POST['pam_account']['psw_confirm'],$message)){
        $this->end(false, $message);
      }

      $saveData = $_POST;
      $saveData = $userPassport->pre_signup_process($saveData);
      if( !$member_id = $userPassport->save_members($saveData,$msg) ){
          $this->end(true, app::get('b2c')->_('添加失败！'));
      }
      //增加会员同步 2012-5-15
      if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
          $member_rpc_object->createActive($member_id);
      }
      $this->end(true, app::get('b2c')->_('添加成功！'));
    }

      function regitem(){
        $this->display('member/member_regitem.html');
      }

      function send_email(){
        if($_POST['isSelectedAll'] == '_ALL_'){
          $view_filter = $this->get_view_filter('b2c_ctl_admin_member','b2c_mdl_members');
          $aMember = array();
          $obj_member = app::get('b2c')->model('members');
          $aData = $obj_member->getList('member_id',$view_filter);
          foreach((array)$aData as $key => $val){
            $aMember[] = $val['member_id'];
          }
        }
        else{
          $aMember = $_POST['member_id'];
        }
        $aEmail = array();
        foreach( $aMember as $mid){
          $aEmail[] = $this->get_email($mid);
        }
        $this->pagedata['aEmail'] = json_encode($aEmail);
        $this->page('admin/messenger/write_email.html');
      }

    function send_msg(){
        if($_POST['isSelectedAll'] == '_ALL_'){
            $aMember = array();
            $view_filter = $this->get_view_filter('b2c_ctl_admin_member','b2c_mdl_members');
            $obj_member = app::get('b2c')->model('members',$view_filter);
            $aData = $obj_member->getList('member_id');
            foreach((array)$aData as $key => $val){
                $aMember[] = $val['member_id'];
            }
        }
        else{
            $aMember = $_POST['member_id'];
        }
        $this->pagedata['aMember'] = json_encode($aMember);
        $this->page('admin/messenger/write_msg.html');
    }

    function send_sms(){
        $obj_member = app::get('b2c')->model('members');
        $params = kernel::single('base_component_request')->get_post();
        $response = kernel::single('base_component_response');
        if($params['isSelectedAll'] == '_ALL_'){
            $aMember = array();
            $view_filter = $this->get_view_filter('b2c_ctl_admin_member','b2c_mdl_members');
            $aData = $obj_member->getList('member_id',$view_filter);
            foreach($aData as $val){
                $aMember[] = $val['member_id'];
            }
        }
        else{
            $aMember = $params['member_id'];
        }
        foreach( $aMember as $mid){
            $row = app::get('pam')->model('members')->getList('login_account',array('login_type'=>'mobile','member_id' => $mid));
            if($row[0]['login_account']){
                $mobile_number[] = $row[0]['login_account'];
            }else{
                $noMobile[] = $mid;
            }
        }

        if($noMobile) {
            $account = kernel::single('pam_mdl_members')->getList('login_account',array('login_type'=>'local','member_id'=>$noMobile));
            $this->pagedata['noMobile'] = $account;
        }

        $this->pagedata['mobile_number'] = json_encode($mobile_number);
        $this->page('admin/messenger/write_sms.html');
    }
    //判断短信内容
    function checkReg($params){
        $arr = array(
            '【', '】', 
            );
       
        if ((strstr($params, $arr[0]) && (strstr($params, $arr[1]))) != false)
        {
            return 'false';
        }
        
        return $params;
       
    }
    function sms_queue(){
       $this->begin();
       $member_obj = $this->app->model('members');
       $mobile_number = json_decode($_POST['mobile_number']);

       if(!$mobile_number) $this->end(false,app::get('b2c')->_('所选会员都没有填写手机号码'));
       $mobile_number = array_unique($mobile_number);

       $mobile_number = array_chunk($mobile_number,200,false);
       $_POST['sendType'] = 'fan-out';
       //判断短信内容
       $content=$this->checkReg($_POST['content']);
       if($content=='false')
       {    
            $this->end(false,app::get('b2c')->_('不能含有非法字符'));
        }
        
       foreach($mobile_number as $m){
           $params = array(
               'mobile_number' => implode(',',(array)$m),
               'data' => $_POST);

           if(!system_queue::instance()->publish('b2c_tasks_sendsms', 'b2c_tasks_sendsms', $params)){
                $this->end(false,app::get('b2c')->_('操作失败！'));
           }
       }
            $this->end(true,app::get('b2c')->_('操作成功！'));
    }

    function msg_queue(){
       $this->begin();
       $member_obj = $this->app->model('members');
       $aMember = json_decode($_POST['arrMember']);
       unset($_POST['arrMember']);
       foreach($aMember as $key=>$val){
           $login_name = kernel::single('b2c_user_object')->get_member_name(null,$val); 
           $params = array(
               'member_id'=>$val,
               'data' =>$_POST,
               'name' => $login_name,
           );

           if(!system_queue::instance()->publish('b2c_tasks_sendmsg', 'b2c_tasks_sendmsg', $params)){
               $this->end(false,app::get('b2c')->_('操作失败！'));
           }
       }
       $this->end(true,app::get('b2c')->_('操作成功！'));
    }

    function insert_queue(){
        #$this->begin('index.php?app=b2c&ctl=admin_member&act=index');
        $this->begin();
        $aEmail = json_decode($_POST['aEmail']);
        $service = kernel::service("b2c.messenger.email_content");
        if(is_object($service))
        {
            if(method_exists($service,'get_content'))
                $_POST['content'] = $service->get_content($_POST['content']);
        }
        $content = trim($_POST['content'],'&nbsp;');
        if(empty($content)){
            $this->end(false,app::get('b2c')->_('邮件内容不能为空！'));
        }
        foreach($aEmail as $key=>$val){
            $params = array(
                'acceptor'=>$val,
                'body' =>$_POST['content'],
                'title' =>$_POST['title'],
            );
            if(!system_queue::instance()->publish('b2c_tasks_sendemail', 'b2c_tasks_sendemail', $params)){
                $this->end(false,app::get('b2c')->_('操作失败！'));
            }
        }
        $this->end(true,app::get('b2c')->_('操作成功！'));
  }

   function get_email($member_id){
       $obj_member = app::get('pam')->model('members');
       $sdf = $obj_member->getList('login_account',array('login_type'=>'email','member_id'=>$member_id));
       return $sdf[0]['login_account'];
  }

  function chkpassword($member_id=null){
    $member = $this->app->model('members');
    if($_POST){
        $userPassport = kernel::single('b2c_user_passport');
        $this->begin();
        $member_id = $_POST['member_id'];
        if ( !$userPassport->check_passport($_POST['newPassword'],$_POST['confirmPassword'],$msg) ){
            $this->end(false,$msg);
        }

        if ( !$userPassport->reset_passport($member_id,trim($_POST['newPassword'])) ){
            $msg=app::get('b2c')->_('密码修改失败！');
            $this->end(false,$msg);
        }

        $arr_colunms = $userPassport->userObject->get_pam_data('*',$member_id);
        $aData['email'] = $arr_colunms['email'];
        $aData['uname'] = $arr_colunms['local'] ? $arr_colunms['local'] : $arr_colunms['mobile'];
        $aData['passwd'] = $data['passwd'];

        //发送邮件或者短信
        $obj_account=$this->app->model('member_account');
        $obj_account->fireEvent('chgpass',$aData,$member_id);
        $msg=app::get('b2c')->_('密码修改成功！');
        $this->end(true,$msg);
    }
    $this->pagedata['member_id'] = $member_id;
    $this->display('admin/member/chkpass.html');
  }

   public function pagination($current,$count,$get){ //本控制器公共分页函数
        $app = app::get('b2c');
        $render = $app->render();
        $ui = new base_component_ui($this->app);
        //unset($get['singlepage']);
        $link = 'index.php?app=b2c&ctl=admin_member&act=ajax_html&id='.$get['id'].'&finder_act='.$get['page'].'&'.$get['page'].'=%d';
        $this->pagedata['pager'] = $ui->pager(array(
                'current'=>$current,
                'total'=>ceil($count/$this->pagelimit),
                'link' =>$link,
                ));
    }

    public function ajax_html()
    {
        $finder_act = $_GET['finder_act'];
        $html = $this->$finder_act($_GET['id']);
        echo $html;
    }

    public function detail_point($member_id=null)
        {
        if(!$member_id) return null;
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $mem_point = $this->app->model('member_point');
         if($member_id)
         {
            $row = $mem_point->getList('id',array('member_id' => $member_id));
            $count = count($row);
        }
        $data = $this->member_model->dump($member_id,'*',array('score/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $this->pagedata['member'] = $data;
        $this->pagedata['event'] = $data['score']['event'];
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_point';
        $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/member/page_point_list.html');
    }

    public function detail_advance($member_id=null)
    {
        if(!$member_id) return null;
        $nPage = $_GET['detail_advance'] ? $_GET['detail_advance'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $mem_adv =  $this->app->model('member_advance');
           $data = $this->member_model->dump($member_id,'*',array('advance/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $items_adv = $data['advance']['event'];
        if($member_id){
             $row = $mem_adv->getList('log_id',array('member_id' => $member_id));
             $count = count($row);
        }
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_advance';
        $this->pagination($nPage,$count,$_GET);
        $this->pagedata['items_adv'] = $items_adv;
        $this->pagedata['member'] = $this->member_model->dump($member_id,'advance');
        echo $this->fetch('admin/member/page_advance_list.html');
    }

    public function detail_order($member_id=null)
    {
        if(!$member_id) return null;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $orders = $this->member_model->getOrderByMemId($member_id,$this->pagelimit*($nPage-1),$this->pagelimit);
        $order =  $this->app->model('orders');
        if($member_id){
         $row = $order->getList('order_id',array('member_id' => $member_id));
         $count = count($row);
        }
        foreach($orders as $key=>$order1){
         $orders[$key]['status'] = $order->trasform_status('status',$orders[$key]['status']);
         $orders[$key]['pay_status'] = $order->trasform_status('pay_status',$orders[$key]['pay_status'] );
         $orders[$key]['ship_status'] = $order->trasform_status('ship_status', $orders[$key]['ship_status']);
        }
        $this->pagedata['orders'] = $orders;
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_order';
        $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/member/page_order.html');
    }

    public function detail_msg($member_id=null)
    {
        if(!$member_id) return null;
        $member_id = intval($member_id);
        $nPage = $_GET['detail_msg'] ? $_GET['detail_msg'] : 1;
        $this->db = kernel::database();
        $_count_row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).')');
        $row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).') limit '.$this->pagelimit*($nPage-1).','.$this->pagelimit);
        $count = count($_count_row);
        $this->pagedata['msgs'] =  $row;
        if($_GET['page']) unset($_GET['page']);
         $_GET['page'] = 'detail_msg';
         $this->pagination($nPage,$count,$_GET);
        echo $this->fetch('admin/member/member_msg.html');
    }

    public function license(){
        if($_POST['license']){
            $this->begin();
            app::get('b2c')->setConf('b2c.register.setting_member_license',$_POST['license']);
            $this->end(true, app::get('wap')->_('当前配置修改成功！'));
        }
        $this->pagedata['license'] = app::get('b2c')->getConf('b2c.register.setting_member_license'); 
        $this->page('admin/member/member_license.html');
    } 

    public function privacy(){
        if($_POST['privacy']){
            $this->begin();
            app::get('b2c')->setConf('b2c.register.setting_member_privacy',$_POST['privacy']);
            $this->end(true, app::get('b2c')->_('当前配置修改成功！'));
        }
        $this->pagedata['privacy'] = app::get('b2c')->getConf('b2c.register.setting_member_privacy'); 
        $this->page('admin/member/member_privacy.html');
    }

}
