<?php

class b2c_service_site_theme_helper
{

    public function function_header()
    {
        $debug = defined('DEBUG_CSS') && constant('DEBUG_CSS');
        $ver = kernel::single('base_component_ui')->getVer($debug);
        $path = app::get('b2c')->res_full_url;
        if(!$debug) return '<link rel="stylesheet" href="'.$path.'/css_mini/basic.min.css'.$ver.'" />';
        else return '<link rel="stylesheet" href="'.$path.'/css/base.css'.$ver.'" />'.
               '<link rel="stylesheet" href="'.$path.'/css/theme.css'.$ver.'" />';
    }//End Function

}//End Class
