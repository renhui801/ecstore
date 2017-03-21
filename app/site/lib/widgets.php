<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class site_widgets{

    private static $__last_modified = null;

    
    static public function set_last_modify() {
        $last_modified = time();
        if (app::get('site')->setConf('widgets_css_last_modify', $last_modified )){
            self::$__last_modified = $last_modified;
            return true;
        }
        return false;
    }

    static public function get_last_modify() {
        if (!isset(self::$__last_modified)) {
            self::$__last_modified = app::get('site')->getConf('widgets_css_last_modify');
        }
        return self::$__last_modified;
    }

    static public function store_widgets_css($tmpl, $css){
        $last_modified = time();
        $data = array('last_modified' => $last_modified,
                      'css' => $css);
        return base_kvstore::instance('site_themes')->store('site_widgets_css_'.$tmpl , $data);
        
    }

    static public function fetch_widgets_css($tmpl, &$css, &$last_modified){
        if (base_kvstore::instance('site_themes')->fetch('site_widgets_css_'.$tmpl, $data)){
            $last_modified = $data['last_modified'];
            $css = $data['css'];
            return true;
        }
        return false;
    }
}
