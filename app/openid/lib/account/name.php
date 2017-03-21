<?php
class openid_account_name{

    public function get_login_name($module_uid=null){
        if(!$module_uid) return '';
        if($pam_account = app::get('pam')->model('auth')->getList('*',array('module_uid' => $module_uid))){
             $members_model = app::get('b2c')->model('members');
             $data = $members_model->getList('name',array('member_id' => $pam_account[0]['account_id']));
             if(!$data){
                   return $pam_account['login_name'];
             }else{
                   return $data[0]['name'];
             }
        }else{
            return $module_uid;
        }
    }
}
?>
