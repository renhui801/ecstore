<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class emailbus_tasks_sendemail extends base_task_abstract implements base_interface_task{
    private $limit = 100;

    function __construct(&$app) {
        $this->app = app::get('emailsubs');
    }
    
    public function exec($params=null){
        $emailaddrModel = $this->app->model('emailaddr');
        //邮件模板
        $emailtmplModel = $this->app->model('emailtmpl');
        //会员模板
        $tmpl_member = $emailtmplModel->getEmailTmpl('mem');
        //非会员模板
        $tmpl_unmember = $emailtmplModel->getEmailTmpl('unmem');

        //选择的邮件内容
        $emailcontModel = $this->app->model('emailcont');
        $emailcont = $emailcontModel->dump($params['selTmpl']);

        //邮件配置
        $obj_emailconf = kernel::single('desktop_email_emailconf');
        $aTmp = $obj_emailconf->get_emailConfig();

        //发邮件
        $email = kernel::single('desktop_email_email');
  
        $cursor_id = 0;
        while ($emailaddrList = $emailaddrModel->getList('ea_id,ea_email,uname',null,intval($cursor_id),$this->limit)){

            foreach($emailaddrList as $key=>$value){
                //邮件模板+内容
                if($value['uname']) {
                    $body = $emailtmplModel->dealwithTmpl($emailcont['ec_content'],$tmpl_member,$value['ea_email'],$value['uname']);
                }else{
                    $body = $emailtmplModel->dealwithTmpl($emailcont['ec_content'],$tmpl_unmember,$value['ea_email']);
                }

                $subject = $emailcont['ec_title'];
                $email->ready($aTmp);
                $res = $email->send($value['ea_email'],$subject,$body,$aTmp);
            }

            $cursor_id= intval($cursor_id) + $this->limit;
            if (count($emailaddrList) < $this->limit) {
                break;
            }
        }
    }
}



