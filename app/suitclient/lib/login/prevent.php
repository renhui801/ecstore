<?php
class suitclient_login_prevent
{
    function pre_auth_uses()
    {

    } 

    //屏蔽原来的登录向oauth authenticate服务器验证是否登录
    //如果认证服务检查已登录应该反回一个code
    function login_verify()
    {
        $model = app::get('suitclient') -> model('server');
	$server = $model->getList('*');
	if(empty($server) )
	{
		return;
	}
        kernel::single('base_session')->start();
        $params = !empty($_GET['params']) ? json_decode($_GET['params'], true) : array();
        if(isset($params['state']) && $params['state'] == $_SESSION['state']) {
            //从oauth服务器里跳转回来
            unset($_SESSION['state']);
            $code = $params['code'];
            if(!$code) {
                echo "can't get access code!";
                exit();
            }

            //拿token
            $accessTokenExchangeParams = array(
                    'client_id' => app::get('suitclient')->getConf('client_id'),
                    'client_secret' => app::get('suitclient')->getConf('client_secret'), 
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => app::get('desktop')->base_url(1),
                    );
            $http = kernel::single('base_httpclient');
            $url = app::get('suitclient')->getConf('oauthtoken');
            $response = $http->post($url, $accessTokenExchangeParams);
            if(!$response) {
                echo 'can\'t get access_token';
                exit();
            }
            $result = json_decode($response, true);
            $access_token = $result['access_token'];

            //拿userinfo, 并模拟登录

            $url = app::get('suitclient')->getConf('userinfo').'?access_token='.$access_token; 
            $response = $http->get($url);
            if(!$response) {
                echo 'access deny!';
                exit();
            }
            $result = json_decode($response, true);
            $model = app::get('pam')->model('account');
            if(!empty($result['account'])) {
                $user = $model->getList('account_id', array('login_name'=>$result['account'], 'account_type'=>'shopadmin'));
                if(empty($user)) {
                    echo '用户不存在';
                    exit();
                }
                $user_id = $user[0]['account_id']; 
                kernel::single('base_session')->start();
                if(!empty($_SESSION['redirect_uri'])) {
                        $url = $_SESSION['redirect_uri'];
                } else {
                        $url =app::get('desktop')->base_url(1);
                }
                $_SESSION = array (
                        'account' => 
                        array (
                            'shopadmin' => $user_id,
                             'scope' => 'suite',
                            ),
                        'type' => 'shopadmin',
                        'login_time' => time(),
                        'message' => '',
                        );
                header('Location:'.$url);
                exit();
            }

        } else {

            //拿code
            $_SESSION['state'] = md5(rand());
            $api_url = app::get('suitclient')->getConf('login');
            /*
            $pre_url = kernel::router()->gen_url($_GET,1);
            $pre_url_array = parse_url($pre_url);
            parse_str($pre_url_array['query'],$query_info);
            if(isset($query_info['url'])&&$query_info['url']){
                $redirect_uri = base64_decode($query_info['url']);
            }else{
                $redirect_uri = app::get('desktop')->base_url(1);
            }*/

            $data = array(
                    'response_type' => 'code',
                    'client_id' => app::get('suitclient')->getConf('client_id'),
                    //'redirect_uri' => app::get('desktop')->base_url(1),
                    'redirect_uri' => !empty($_GET['url']) ? base64_decode($_GET['url']) : app::get('desktop')->base_url(1),
                    'scope' => 'get_user_info',
                    'state' => $_SESSION['state'] ,
                    );  
            // mini login 
            $parts = parse_url($data['redirect_uri']);
            if(!empty($parts['query'])) {
                parse_str($parts['query'], $querys);
            }
            if(isset($querys['suitelogin']) && $querys['suitelogin'] == 'mini') {
                $api_url = str_replace('login', 'minilogin', $api_url);
                $_SESSION['redirect_uri'] = $data['redirect_uri'];
            }

            $data = http_build_query($data);
            $url = $api_url.'?'.$data;
	    if( ! preg_match('/^(http|https)/', $url) ) {
		$url = 'http://'.$url;
	    }

            header("Location:$url");
            exit(); 
        }
    }
}
