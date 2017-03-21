<?php
class b2c_service_member_autocomplete{
    function get_data($key,$cols){
        if(!$key) return null;
        $obj_pam = app::get('pam')->model('members');
        $filter['login_account|head'] = $key;
        $result = $obj_pam->getList('member_id,login_account',$filter);
        foreach((array)($result) as $k=>$v){
            $return[$k]['login_name'] = $v['login_account'];
            $return[$k]['account_id'] = $v['member_id'];
        } 
        return $return;
    }
}
