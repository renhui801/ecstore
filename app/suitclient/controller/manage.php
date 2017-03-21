<?php
    class suitclient_ctl_manage extends desktop_controller 
    {
        function __construct($app) {
            $this->app =$app;
		if(define('SAAS_MODE')) {
			return;
		}
        }
	
	function index () {
		$this->pagedata['info'] = app::get('suitclient')->getConf('info');
		$this->display('index.html');
	}

	function save() {
		$this->begin();
		if(empty($_POST['setting'])) {
			echo '激活码为空';
			exit();
		}
		app::get('suitclient')->setConf('info',$_POST['setting']);
		$info = base64_decode($_POST['setting']);
		$info = unserialize($info);
		if(hash_hmac('sha256', $info['code'], $_SERVER['HTTP_HOST']) !== $info['screct']) {
		$this->end(false, '激活失败');
	}
		$init = new suitclient_saas_init();
		$init->setup(unserialize($info['code']));
                $this->end(true, '激活成功');
	}
        
	/*
        function index () {
            $model = $this->app->model('server');
            $server = $model->getList('*');
            if(!empty($server)) {
                $server = $server[0];
            }
            $this->pagedata['server'] = $server;
            $this->display('index.html');
        }
        
        function save()
        {
            $this->begin();
            $model = $this->app->model("server");
            if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $_POST['url'])) {
                $this->end(false, 'URL不合法');
            }
            if(count($server = $model->getList('*')) > 0) {
                $return = $model->update($_POST, array('id'=>$server[0]['id']));
            } else {
                $return = $model->save($_POST);
            }
            if($return) {
                $this->end(true, '保存成功');
            } else {
                $this->end(false, '保存失败');
            }
            exit();
        }
	*/
    
    }
