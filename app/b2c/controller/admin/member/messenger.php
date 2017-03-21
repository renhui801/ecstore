<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

define('MANUAL_SEND','MANUAL_SEND');
class b2c_ctl_admin_member_messenger extends desktop_controller {

    //var $workground = 'b2c.workground.member';

     public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->path[] = array('text'=>app::get('b2c')->_('邮件短信配置'));
        $messenger = $this->app->model('member_messenger');
        $action = $messenger->actions();
        foreach($action as $act=>$info){
            $list = $messenger->getSenders($act);
            foreach($list as $msg){
                $this->pagedata['call'][$act][$msg] = true;
            }
        }
        $setSmssign = app::get('b2c')->getConf('setSmsSign');
        $this->pagedata['smsSign'] = $setSmssign;
        $sms = kernel::single('b2c_messenger_sms');
        $this->pagedata['actions'] = $action;
        $this->pagedata['sms_url'] = $sms->extraVars();
        $this->_show('admin/messenger/index.html');
    }

    function setSmsSign(){
        $setSmsSign = app::get('b2c')->getConf('setSmsSign');
        if(!is_array($setSmsSign)){
            app::get('b2c')->setConf('setSmsSign','');
        }
        $this->pagedata['sign'] = is_array($setSmsSign) ? $setSmsSign['sign'] : '';
        $this->page('admin/messenger/setsms.html');
    }

    function saveSmsSign(){
        if(mb_strlen(urldecode(trim($_POST['sign'])),'utf-8') > 8 || mb_strlen(urldecode(trim($_POST['sign'])),'utf-8') < 2 ){
            $this->begin('index.php?app=b2c&ctl=admin_member_messenger&act=setSmsSign');
            $this->end(false,app::get('b2c')->_('签名长度为2到8字'));
        }
        //校验签名
        $this->check_str($_POST['sign']); 
        //$sign=$this->checkReg($_POST['sign']);
        $sign=$_POST['sign'];
        $signs='【'.$sign.'】';
        $entid = base_enterprise::ent_id();
        $passwd=base_enterprise::ent_ac();
        $params = array(
            'shopexid' => $entid,
            'content' => $signs,
            'passwd' => $passwd,
        );
        $url = 'https://openapi.shopex.cn/api';
        if(defined('SMS_SNDBOX') && SMS_SNDBOX)
        {
             $url = 'https://openapi.shopex.cn/api-sandbox';
        }
        
        $core_http = kernel::single('base_prism');
        $core_http->app_key='xft7toho';
        $core_http->app_secret='zoj66zxqjkq4is3xx762';
        $core_http->base_url=$url;
        //判断是添加还是修改
        $setSmsSign=app::get('b2c')->getConf('setSmsSign');
       //添加签名
        if(empty($setSmsSign['sign']))
        {
            $result = $core_http->post('/addcontent/new',$params);
           
        }
        else
        { //修改签名
            $params = array(
                'shopexid' => $entid,
                'passwd' => $passwd,
                'old_content'=>'【'.$setSmsSign['sign'].'】',
                'new_content' => $signs,
            );
            $result = $core_http->post('/addcontent/update',$params);
        }

        $response = json_decode($result,true);

        if(!($response['res'] == 'succ'))
        {   
            //兼容目前出现的“签名不存在”问题
            if($response['code'] == '2010')
            {
                app::get('b2c')->setConf('setSmsSign', null);
            }
            $this->begin('index.php?app=b2c&ctl=admin_member_messenger&act=setSmsSign');
            $this->end(false,app::get('b2c')->_($response['data']));
        }else{
            $array=array(
                'sign'=>trim($sign),
            ); 
            $this->begin();
            app::get('b2c')->setConf('review',$response['data']['review']);
            app::get('b2c')->setConf('setSmsSign', $array);
            $this->end(true,app::get('b2c')->_('保存成功'));
        }
    }
    //验证签名
    function check_str($params){
        $arr=array('天猫','tmall','淘宝','taobao','1号店','易迅','京东','亚马逊','test','测试');
        for ($i=0; $i <count($arr) ; $i++) 
        { 
            if(strstr(strtolower($params),$arr[$i] ))
            {   
                $this->begin('index.php?app=b2c&ctl=admin_member_messenger&act=setSmsSign');
                $this->end(false,app::get('b2c')->_('非法签名'));
            }
        }
    }
    //验证签名
   /* function checkReg($params){
        $arr = array(
            '~', '!', '@', '#', '$', '%', '^', '&', '*', '_', '+', '|', '-', '=', '\\',
            '{', '}', '[', ']', ':', ';', '"', '\'', '<', '>', ',', '.', '?', '/', '“', '”',
            '’', '‘', '【', '】', '~', '！', '￥', '……', '——', '、', '《', '》', '。',
            PHP_EOL, chr(10), chr(13), "\t", chr(32),
            );
        foreach ($arr as $k)
        {
            if (strpos($params, $k) !== false)
            {
                $this->begin('index.php?app=b2c&ctl=admin_member_messenger&act=setSmsSign');
                $this->end(false,app::get('b2c')->_('签名不能有特殊字符'));
            }
        }
        return $params;
    }
   */

    function edtmpl($action,$msg){
        $messenger = $this->app->model('member_messenger');
        $info = $messenger->getParams($msg);
        if($this->pagedata['hasTitle'] = $info['hasTitle']){
            $this->pagedata['title'] = $messenger->loadTitle($action,$msg);
        }
        
        $this->pagedata['body'] = $messenger->loadTmpl($action,$msg);
        $this->pagedata['type'] = $info['isHtml']?'html':'textarea';
        $this->pagedata['messenger'] = $msg;
        $this->pagedata['action'] = $action;
        $actions = $messenger->actions();
        $this->pagedata['varmap'] = $actions[$action]['varmap'];
        $this->pagedata['action_desc'] = $actions[$action]['label'];
        $this->pagedata['msg_desc'] = $info['name'];
        $this->singlepage('admin/messenger/edtmpl.html');
    }

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
    function viewtmpl($action,$msg){
        $messenger = $this->app->model('member_messenger');
        $this->pagedata['body'] = $messenger->loadTmpl($action,$msg);
        $setSmsSign = app::get('b2c')->getConf('setSmsSign');
        $this->pagedata['smssign'] = is_array($setSmsSign) ? $setSmsSign['sign'] : '';
        $this->page('admin/messenger/viewtmpl.html');
    }

    function saveTmpl(){
        $this->begin();
        $messenger = $this->app->model('member_messenger');
        $content=$this->checkReg($_POST['content']);
        if($_POST['messenger']=='b2c_messenger_sms')
        {
            if($content=='false')
            {    
                $this->end(false,app::get('b2c')->_('含有非法字符'));
            }
        }
       
       
        $ret = $messenger->saveContent($_POST['actdo'],$_POST['messenger'],array(
            'content'=>htmlspecialchars_decode($content),
            'title'=>$_POST['title'],
        ));
        if($ret){
            $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
             $this->end(false,app::get('b2c')->_('操作失败'));
        }
    }

    function save(){
        $this->begin('');
        $messenger = $this->app->model('member_messenger');
        if ($messenger->saveActions($_POST['actdo'])) {
             $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
              $this->end(false,app::get('b2c')->_('操作失败'));
        }
    }

    function outbox($sender){
        $this->path[] = array('text'=>app::get('b2c')->_('发件箱'));
        $messenger = $this->app->model('member_messenger');
        $this->pagedata['oubox'] = $messenger->outbox($sender);
        $this->pagedata['sender']=$sender;
        $this->_show('messenger/outbox.html');
    }

    function _show($tmpl){
        $messenger = $this->app->model('member_messenger');
        $this->pagedata['messenger'] = $messenger->getList();
        $this->pagedata['__show_page__'] = $tmpl;
        $this->page('admin/messenger/page.html');
    }





}
?>
