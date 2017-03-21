<?php

class site_ctl_proinstance extends site_controller 
{
    
    public function index() 
    {
        $this->pagedata['file'] = 'site_proinstance:'.$this->_request->get_param(0);
        $this->page('proinstance.html', true);
    }//End Function
    
	public function get_css()
	{
		$params = $this->_request->get_params(true); 
		$theme = $params[0];
		$tmpl = base64_decode($params[1]);

        $widgets_css_last_modified = site_widgets::get_last_modify();
        if (!site_widgets::fetch_widgets_css($tmpl, $widgets_css, $cache_last_modified) || intval($cache_last_modified)<intval($widgets_css_last_modified)) {
            $this->set_theme($theme); 
            $content = $this->display_tmpl($tmpl,true);
            $style = ''; 
            $__widgets_css = array(); 
            preg_match_all('/<\s*style.*?>(.*?)<\s*\\/\s*style\s*>/is', $content, $matchs); 
            if(isset($matchs[0][0]) && !empty($matchs[0][0])){ 
                $__widgets_css = array_merge($__widgets_css,$matchs[1]); 
            }		 
            $style .= implode("\r\n", array_unique($__widgets_css));
            site_widgets::store_widgets_css($tmpl, $style);
        }else{
            $style = $widgets_css;
        }
        
		$this->_response->set_body($style);
		$this->_response->set_header('Content-type','text/css');
	}//End Function 
}//End Class