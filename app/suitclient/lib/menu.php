<?php
class suitclient_menu implements desktop_interface_controller_content
{
    public function modify(&$html, &$obj)
    {
        $model = app::get('suitclient')->model('server');
        $server = $model->getList('*');
        if(!empty($server)) {
            $server = $server[0];
        } else {
            //还未绑定
            return;
        }
        //mini登录
        if(!empty($_GET['suitelogin']) && $_GET['suitelogin'] == 'mini') {
            return;
        }

        $url = rtrim($server['url'], '/') . '/app/index/client_id/'.app::get('suitclient')->getConf('client_id');
	if( ! preg_match('/^(http|https)/', $url) ) {
		$url = 'http://'.$url;
	}
	
	$script = '';
        kernel::single('base_session')->start();
	if(isset($_SESSION['account']['scope']) && $_SESSION['account']['scope'] == 'suite') {
        $script =<<<SCRIPT
		<style>
		.head-user{display:none}
	</style>
		<script>
		//if(window.top.href != "$url") {
		if(window.top === window.self){
			top.location = "$url";
		}
	</script>"
SCRIPT;
	}
        $html .= $script;
    }

}
