<?php
    /**
        Saas  初始化配置信息以及绑定信息 
    **/
    class suitclient_saas_init
    {
        function __construct() {
            $this->model = app::get('suitclient') -> model('server');
        }
        /*
            $info = array(
                'config' => array(
                    'client_id' => 'oauth client_id',
                    'client_secret' => 'oauth secret', 
                    'syncuser'      => 'sync user api uri',
                    'login'         => 'suitserver login uri',
                    'oauthtoken'    => 'get oauthtoken uri',
                    'userinfo'      => 'get userinfo uri',
                ), 
                'bind' => array(
                    'url' => 'suit server index url',
                    'secret' => 'suit server secret',
                ),
            );
        */
        function setup($info = array()) {
            $config = $info['config'];
            $bind = $info['bind'];
            foreach ( (array) $config as $key => $value) {
                app::get('suitclient')->setConf($key, $value); 
            }
            $this->model->db->exec('TRUNCATE TABLE `sdb_suitclient_server`');
            $this->model->insert($bind); 
        }
    }
