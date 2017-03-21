<?php

class wap_ctl_proinstance extends wap_controller 
{
    
    public function index() 
    {
        $this->pagedata['file'] = 'wap_proinstance:'.$this->_request->get_param(0);
        $this->page('proinstance.html', true);
    }//End Function
    
    public function get_css()
    {
        $params = $this->_request->get_params(true);        
        $theme = $params[0];
        $tmpl = base64_decode($params[1]);
        
        $this->set_theme($theme);
        $content = $this->display_tmpl($tmpl,true);
        
        $style = '';
        $__widgets_css = array();
        preg_match_all('/<\s*style.*?>(.*?)<\s*\/\s*style\s*>/is', $content, $matchs);
        if(isset($matchs[0][0]) && !empty($matchs[0][0])){
            $__widgets_css = array_merge($__widgets_css,$matchs[1]);
        }       
        $style .= implode("\r\n", array_unique($__widgets_css));    
        
        $this->_response->set_body($style);
        $this->_response->set_header('Content-type','text/css');
    }//End Function 

}//End Class
