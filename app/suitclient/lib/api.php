<?php
    class suitclient_api
    {
        function __construct () {
            $this->model = app::get('suitclient')->model('server');
        }

        function check() {
            if(!isset($_POST['hmac'])) {
                $this->msg(array(
                            'error' => 'miss hmac',
                        ));
            }
            $params = $_POST;
            unset($params['hmac']);
            $server = $this->model->getList('*');
            if(empty($server) || !($secret = $server[0]['secret'])) {
                $this->msg(array('error' => 'invalid.'));
            }
            ksort($params);
            if(!hash_hmac('sha256', json_encode($parmas), $secret) === $_POST['hmac']) {
                $this->msg(array('error' => 'invalid!'));
            }
        }

        function ping_back () {
            $this->check();
        }

        function add_user($name) {
            $this->check();

            $model = app::get('pam')->model('account');
            $user = $model->getList('*', array('login_name'=>$name, 'account_type'=>'shopadmin'));
            if(!empty($user)) return;

            $model2 = app::get('desktop')->model('users');
            $user = array('name'=>$name,
                          'status' => 1,
                          'super' => 0,
                          'disabled' => false,
                          'pam_account' => array(
                                'login_name'=>$name,
                                'login_password'=>md5(time().rand()),
                                'account_type' => 'shopadmin',
                            ),
                          'roles'=>array ( array('role_id'=>1) ),
                        );
            $model2->save($user);
        }

        private function msg ($msg = array()) {
            echo json_encode($msg);
            exit();
        }
    }
