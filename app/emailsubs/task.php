<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author chenping<chenping@shopex.cn>
 * @version 2011-8-9 17:45
 */

class emailsubs_task{

    public function __construct(&$app) {
        $this->app = $app;
    }

    /**
     * @access public
     * @description 安装完成后执行操作
     * @param void
     * @return void
     */
    function post_install($options){
        $signupUrl = kernel::base_url(1).'/index.php/passport-signup.html';
        $emailtmplModel = $this->app->model('emailtmpl');
        //会员模板
        $data['et_name']='mem';
        $data['et_content']='<div class="eamilwrap" style="width:600px; margin:0 auto; border:5px #ce2234 solid; padding:20px;font-size:14px; overflow:hidden;"><h2 style="border-bottom:1px #ccc solid; padding:0 0 20px 0;margin-bottom:20px; font-size:14px;">您好{emailsubs_uname}：</h2>{emailsubs_content}<div class="botinfo" style=" border-top:1px #ccc solid; padding-top:20px; text-align:right;">如不想继续收（{shopname}）活动资讯，您可以随时 <a href="{emailsubs_cancel}" target="_blank" style="color:#ce2234;">取消订阅</a></div></div>';
        $emailtmplModel->save($data);

        unset($data);
        //非会员模板
        $data['et_name']='unmem';
        $data['et_content'] = '<div class="eamilwrap" style="width:600px; margin:0 auto; border:5px #ce2234 solid; padding:20px; font-size:14px;overflow:hidden;"><h2 style="border-bottom:1px #ccc solid; padding:0 0 20px 0; margin-bottom:20px; font-size:14px;">您好：</h2>{emailsubs_content}<div class="botinfo" style="border-top:1px #ccc solid; padding-top:20px; text-align:right;"> <p>立即成为<a target="_blank" href="'.$signupUrl.'">网站会员</a></p><p>如不想继续收({shopname})活动资讯，您可以随时 <a href="{emailsubs_cancel}" target="_blank" style="color:#ce2234;">取消订阅</a></p></div></div>';

        $emailtmplModel->save($data);
    }
}
