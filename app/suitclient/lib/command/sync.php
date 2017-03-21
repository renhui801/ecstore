<?php
class suitclient_command_sync extends base_shell_prototype
{
    var $command_sync_user = '同步套件用户';

    public function command_sync_user()
    {
	//kernel::console_output = false;
        $http = kernel::single('base_httpclient');
        $response = $http->get(app::get('suitclient')->getConf('syncuser'));
        if($response) {
            $server_users = json_decode($response);
            $model = app::get('pam')->model('account');
            $result = $model->getList('login_name',  array('account_type'=>'shopadmin'));
            $client_user = array();
            foreach($result as $value) {
                $client_user[] = $value['login_name'];
            }

            $model2 = app::get('desktop')->model('users');
            foreach($server_users as $server_user) {
                if(!in_array($server_user, $client_user)) {
                    $user = array('name'=>$server_user,
                            'status' => 1,
                            'super' => 0,
                            'disabled' => false,
                            'pam_account' => array(
                                'login_name'=>$server_user,
                                'login_password'=>md5(time().rand()),
                                'account_type' => 'shopadmin',
                             ),
                          	'roles'=>array ( array('role_id'=>1) ),
                            );
                    $model2->save($user);

                }

            }
            logger::info('同步成功');
            logger::info('ok.');//不加 "ok."则会弹提示信息并不能自动关闭@lujy
        } else {
            logger::info('同步失败或套件里没有用户');
            logger::info('ok.');
        }
        exit();
    }

}
