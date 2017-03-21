<?php
/**
 * ShopEx licence
 * 会员接口请求crm路由器
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_apiv_exchanges_request_member extends b2c_apiv_exchanges_request
{

    //创建会员推送到crm
    public function createActive($member_id){
        $result = $this->rpc_caller_request($member_id, 'membercreate');
        $result = json_decode($result,true);
        $memberModel = app::get('b2c')->model('members');
        $memberModel->update(array('crm_member_id'=>$result['user']['user_id']),array('member_id'=>$member_id));
    }

    //更新会员信息推送到crm
    public function modifyActive($member_id){
        $userObject = kernel::single('b2c_user_object');
        $data = $userObject->get_members_data(array('members'=>'crm_member_id'),$member_id);

        //crm初始化调用ecstore的时候不会能到crm_member_id
        //而在更新的会员信息，crm需要crm_member_id来更新会员信息
        if($data['members']['crm_member_id']){
            $this->rpc_caller_request($member_id, 'memberupdate');
        }
    }
}
