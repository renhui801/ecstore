<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author chenping<chenping@shopex.cn>
 * @version 2011-8-9 16:01
 */

class emailsubs_ctl_admin_emailsend extends desktop_controller{

    var $workground = 'emailsubs_ctl_admin_emailcont';

    public function __construct($app){
		parent::__construct($app);
        $this->_request = kernel::single('base_component_request');
	}

    /**
     * @access public
     * @description 进入发送邮件页
     * @param void
     * @return void
     */
    public function sendShow() {
        $this->path[] = array('text'=>$this->app->_('订阅邮件预览'));

        $emailcontModel = $this->app->model('emailcont');
        $emailcontList = $emailcontModel->getList('ec_id,ec_title',array('ec_ifsend'=>0),0,10,'ec_addtime DESC');
        $this->pagedata['emailcont'] = $emailcontList;

        $this->page('admin/emailsend/sendShow.html');
    }

    /**
     * @description 邮箱测试页
     * @access public
     * @param void
     * @return void
     */
    public function emailTest($ec_id=0) {
        $this->path[] = array('text'=>$this->app->_('邮箱测试'));
        $emailcontModel = $this->app->model('emailcont');
        $emailcont = $emailcontModel->dump($ec_id);
        $this->pagedata['emailcont'] = $emailcont;
        $this->display('admin/emailsend/test/emailtest.html');
    }

    /**
     * @description 邮箱配置
     * @access public
     * @param void
     * @return void
     */
    public function setting($ec_id) {
        $this->pagedata['options'] = kernel::single('desktop_ctl_email')->getOptions();
        $this->pagedata['messengername'] = "messenger";
        $this->pagedata['ec_id'] = $ec_id;
        $this->display('admin/emailsend/test/config.html');
    }

    /**
     * @description 邮箱配置保存
     * @access public
     * @param void
     * @return void
     */
    function saveCfg(){
       $this->begin();
           foreach($_POST['config'] as $key=>$value){
            app::get('desktop')->setConf('email.config.'.$key,$value);
        }
        $this->end(true,app::get('desktop')->_('配置保存成功'));
    }

    /**
     * @description 发送测试邮件
     * @access public
     * @param void
     * @return void
     */
    public function doTestemail(){
        $this->begin();
        $params = $this->_request->get_post();

        //邮箱配置
        $app = app::get('desktop');

        $emailconf = kernel::single('desktop_email_emailconf');
        $aTmp = $emailconf->get_emailConfig();
        $aTmp['acceptor']       = $params['email'];                                         //收件人邮箱

        $emailcontModel = $this->app->model('emailcont');
        $emailcont = $emailcontModel->dump($params['ec_id']);

        //邮件标题
        $subject =$emailcont['ec_title'];

        //邮件模板
        $emailtmplModel = $this->app->model('emailtmpl');
        $emailtmpl =  $emailtmplModel->dump('unmem');

        $body = $emailtmplModel->dealwithTmpl($emailcont['ec_content'],$emailtmpl['et_content'],$aTmp['acceptor']);

        switch ($aTmp['sendway']){
            case 'smtp':
                $email = kernel::single('desktop_email_email');
                $loginfo = $app->_("无法发送测试邮件，下面是出错信息：");
                if ($email->ready($aTmp)){
                    $res = $email->send($aTmp['acceptor'],$subject,$body,$aTmp);
                    if ($res){
                        $loginfo = $app->_("已成功发送一封测试邮件，请查看接收邮箱。");
                        $this->endonly();
                        $this->page('admin/emailsend/test/success.html');
                        exit;
                    }elseif ($email->errorinfo){
                        $err=$email->errorinfo;
                        $loginfo .= "<br>".$err['error'];
                    }
                }
                else{
                    $loginfo .= "<br>".var_export($email->smtp->error,true);
                }
                $this->end(false,$loginfo);
                break;
            case 'mail':
                ini_set('SMTP', $aTmp['smtpserver']);
                ini_set('smtp_port',$aTmp['smtpport']);
                ini_set('sendmail_from',$aTmp['usermail']);
                $email = kernel::single('desktop_email_email');
                $subject=$email->inlineCode($subject);
                $header = array(
                    'Return-path'=>'<'.$aTmp['usermail'].'>',
                    'Date'=>date('r'),
                    'From'=>$email->inlineCode(app::get('site')->getConf('site.name')).'<'.$aTmp['usermail'].'>',
                    'MIME-Version'=>'1.0',
                    'Content-Type'=>'text/html; charset=UTF-8; format=flowed',
                    'Content-Transfer-Encoding'=>'base64'
                );
                $body=chunk_split(base64_encode($body));
                $header=$email->buildHeader($header);
                if(mail($aTmp['acceptor'], $subject, $body, $header)){
                    $this->end(true,$app->_("发送成功！"));
                }else{
                    $this->end(true,$app->_("发送失败，请检查邮箱配置！"));
                }
                break;
        }
    }

    /**
     * @description 订阅邮件发送函数
     * @access public
     * @param void
     * @return void
     */
    public function toSend() {
        $this->begin();
        $params = $this->_request->get_post();

        if(empty($params['selTmpl'])) {
            $this->end(false,$this->app->_('请选择邮件模板'));
        }

        if(empty($params['emailtype'])) {
            $this->end(false,$this->app->_('请发送邮件地址方式'));
        }

        if($params['emailtype']==2 && empty($params['emailsel'])) {
            $this->end(false,$this->app->_('请选择自定义邮件地址'));
        }


        $emailcontModel = $this->app->model('emailcont');

        switch($params['emailtype']) {
            case 1:      //选取所有订阅邮箱地址
                $params = array('selTmpl'=>$params['selTmpl']); //选择的模板
                // 'worker'=>'emailsubs_queue.send_mail',

                //插入列表状态
                if(!system_queue::instance()->publish('emailbus_tasks_sendemail', 'emailbus_tasks_sendemail', $params)){
                    $this->end(false,$this->app->_('操作失败！'));
                }

                //更新邮件状态
                $emailcontModel->update(array('ec_ifsend'=>'1','ec_sendtime'=>time()),array('ec_id'=>$params['selTmpl']));

                $this->end(true,$this->app->_('操作成功！'));
                break;
            case 2:      //自定义选取订阅邮箱地址
                //邮件内容
                $emailcont = $emailcontModel->dump($params['selTmpl']);

                //邮件模板
                $emailtmplModel = $this->app->model('emailtmpl');

                //会员模板
                $tmpl_member = $emailtmplModel->getEmailTmpl('mem');
                //非会员模板
                $tmpl_unmember = $emailtmplModel->getEmailTmpl('unmem');

                $emailaddrModel = $this->app->model('emailaddr');
                $emailaddrList = $emailaddrModel->getList('ea_id,ea_email,uname',array('ea_id'=>$params['emailsel']));
                foreach($emailaddrList as $key=>$value){
                    if($value['uname']) {
                    $body = $emailtmplModel->dealwithTmpl($emailcont['ec_content'],$tmpl_member,$value['ea_email'],$value['uname']);
                    }else{
                    $body = $emailtmplModel->dealwithTmpl($emailcont['ec_content'],$tmpl_unmember,$value['ea_email']);
                    }

                    $params = array(
                        'acceptor'=>$value['ea_email'],
                        'body' =>$body,
                        'title' =>$emailcont['ec_title'],
                    );
                    if(!system_queue::instance()->publish('b2c_tasks_sendemail', 'b2c_tasks_sendemail', $params)){
                        $this->end(false,$this->app->_('操作失败！'));
                    }
                }
                $emailcontModel->update(array('ec_ifsend'=>'1','ec_sendtime'=>time()),array('ec_id'=>$params['selTmpl']));
                $this->end(true,$this->app->_('操作成功！'));
                break;
            default:
                $this->end(false,$this->app->_('操作失败！'));
                break;
        }

    }

}
