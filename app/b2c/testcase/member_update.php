<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class member_update extends PHPUnit_Framework_TestCase
{
    /*
     * author guzhengxiao
     */
    public function setUp()
    {
        $this->model = app::get('b2c')->model('members');
        $this->pamAccountModel = app::get('pam')->model('account');
        $this->pamModel = app::get('pam')->model('members');
    }

    public function testUpdate(){
        $userPassport = kernel::single('b2c_user_passport');  
        $pamMemberData = $this->pamAccountModel->getList('*',array('account_type'=>'member')); 
        foreach((array)$pamMemberData as $row){
            $memberData = $this->model->getList('email,mobile',array('member_id'=>$row['account_id'])); 
            $login_type = $userPassport->get_login_account_type($row['login_name']);
            $savePamMemberData = array(
              'member_id' => $row['account_id'],  
              'login_account' => $row['login_name'],
              'login_type' => $login_type,
              'login_password' => $row['login_password'],
              'password_account' => $row['login_name'],
              'createtime' => $row['createtime'],
              'disabled' => $login_type == 'mobile' ? 'true':'false',
            );
            $flag = array();
            $flag = $this->pamModel->getList('*',array('login_account'=>$row['login_name']));
            if(empty($flag)){
              if( !$this->pamModel->insert($savePamMemberData) ){
                echo 'pam 会员数据更新失败--'.$savePamMemberData['login_account']."\r\n";
              }
            }

            $emailData = array(
              'member_id' => $row['account_id'],  
              'login_account' => $memberData[0]['email'],
              'login_type' => 'email',
              'login_password' => $row['login_password'],
              'password_account' => $row['login_name'],
              'createtime' => $row['createtime'],
              'disabled' => 'true',
            );
            $emailaccountflag = array();
            $emailmemberflag = array();
            $pkeyflag = array();
            $emailaccountflag = $this->pamAccountModel->getList('*',array('login_name'=>$memberData[0]['email'],'account_type'=>'member'));
            $emailmemberflag = $this->pamModel->getList('*',array('login_account'=>$memberData[0]['email'],'login_type'=>'email'));
            $pkeyflag = $this->pamModel->getList('*',array('member_id'=>$row['account_id'],'login_type'=>'email'));
            if( $memberData[0]['email'] && $memberData[0]['email'] != $row['login_name'] && empty($pkeyflag) && empty($emailaccountflag) && empty($emailmemberflag) ){
                if(!$this->pamModel->insert($emailData)){
                  echo 'email 会员数据更新失败--'.$emailData['login_account']."\r\n";
                }
            }

            $mobileData = array(
              'member_id' => $row['account_id'],  
              'login_account' => $memberData[0]['mobile'],
              'login_type' => 'mobile',
              'login_password' => $row['login_password'],
              'password_account' => $row['login_name'],
              'createtime' => $row['createtime'],
              'disabled' => 'true',
            );
            $accountflag = array();
            $memberflag = array();
            $mobilepkeyflag = array();
            $accountflag = $this->pamAccountModel->getList('*',array('login_name'=>$memberData[0]['mobile'],'account_type'=>'member'));
            $memberflag = $this->pamModel->getList('*',array('login_account'=>$memberData[0]['mobile'],'login_type'=>'mobile'));
            $mobilepkeyflag = $this->pamModel->getList('*',array('member_id'=>$row['account_id'],'login_type'=>'mobile'));
            if( $memberData[0]['mobile'] && $memberData[0]['mobile'] != $row['login_name'] && empty($mobilepkeyflag) && empty($memberflag) && empty($accountflag) ){
                if( !$this->pamModel->insert($mobileData) ){
                    echo 'mobile 会员数据更新失败--'.$mobileData['login_account']."\r\n";
                }
            }
        }
        echo '会员数据更新成功'."\r\n";
    }

}
