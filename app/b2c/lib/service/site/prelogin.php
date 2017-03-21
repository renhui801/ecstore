<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * 用户登录数据预处理
 */



class b2c_service_site_prelogin
{

    /*
     * 如果用户为邮箱登录则使用邮箱获取到真实的login_name
     * @$data array 登录post的数据
     * */
    public function get_login_name(&$data){
        if(preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i', $data['uname'])){
            $memberid = app::get('b2c')->model('members')->getList('member_id',array('email'=>$data['uname']));
            if($memberid){
                $account = app::get('pam')->model('account')->getList('login_name',array('account_id'=>$memberid[0]['member_id']));
            }
            $data['uname'] = $account[0]['login_name'] ? $account[0]['login_name'] : $data['uname'];
        }
        return true;
    }

}//End class


