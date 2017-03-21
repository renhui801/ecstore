<?php
class suitclient_login_out implements desktop_interface_controller_content
{
    public function modify(&$html, &$obj)
    {
        //原来的退出登录如果不是ajax的,直接放iframe里会出现文件下载
//        @header_remove();
        header("Cache-Control:no-store, no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header('Progma: no-cache');
        header('Content-Type: text/html; charset=utf-8');
        exit();    
    }
}
