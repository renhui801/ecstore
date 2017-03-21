<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class emailsubs_mdl_emailtmpl extends dbeav_model{

    function __construct(&$app){
        parent::__construct($app);
    }

    /**
     * @description 得到完整的邮件内容
     * @access public
     * @param void
     * @return void
     */
    public function dealwithTmpl($content,$tmpl,$email,$uname='') {
        $uname = $uname ? ','.$uname : '';
        $sign = md5(STORE_KEY.$email);
        $cancel_url = kernel::single('site_router')->gen_url(array('app' => 'emailsubs', 'ctl' => 'site_emailaddr','act' => 'cancel','args'=>array($email,$sign),'full'=>true));
        $pattern = array('{emailsubs_content}','{shopname}','{emailsubs_cancel}','{emailsubs_uname}');
        $subject = array($content, app::get('site')->getConf('site.name'),$cancel_url,$uname);
        $emailBody = str_replace($pattern,$subject,$tmpl);

        return $emailBody;
    }

    /**
     * @description 得到邮件模板
     * @access public
     * @param void
     * @return void
     */
    public function getEmailTmpl($type) {
        switch($type) {
            case 'mem':
                $tmpl = $this->dump('mem');
                break;
            case 'unmem':
                $tmpl = $this->dump('unmem');
                break;
            default:
                $tmpl['et_content'] = '{emailsubs_content}';
                break;
        }
        return $tmpl['et_content'];
    }
}
