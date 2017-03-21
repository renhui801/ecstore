<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class site_admin_controller extends desktop_controller
{

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
        $this->_request = kernel::single('base_component_request');
        $this->_response = kernel::single('base_component_response');
    }//End Function

    /*
     * 错误
     * @param string $msg
     */
    public function _error($msg='非法操作')
    {
        header("Content-type: text/html; charset=utf-8");
        echo $msg;exit;
    }//End Function

    protected function check($theme,&$msg='')
    {
        if(kernel::single('site_theme_file')->check($theme,$msg)){
            return true;
        }else{
            return false;
        }
    }//End Function

    /*
     * 跳转
     * @param string $url
     */
    public function _redirect($url)
    {
        $this->_response->set_redirect($url)->send_headers();
    }//End Function


}//End Class
