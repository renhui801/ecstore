<?php
/**
* ShopEx licence
*
* @copyright Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
* @license http://ecos.shopex.cn/ ShopEx License
*/

class init_members extends PHPUnit_Framework_TestCase
{
/*
* author guzhengxiao
*/
public function setUp()
{
// $this->model = app::get('b2c')->model('goods_type');
}


public function testInsert(){
$objM = kernel::single('b2c_user_passport');
$data = array();
for($i=1;$i<=1995;$i++){
$data['pam_account'] = array('login_name'=>'demo'.$i,'login_password'=>'shopex');
$data1 = $objM->pre_signup_process($data);
$objM->save_members($data1,$msg);
echo $data['pam_account']['login_name']."\n";
}
}


}