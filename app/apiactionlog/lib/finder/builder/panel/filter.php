<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class apiactionlog_finder_builder_panel_filter extends desktop_finder_builder_prototype{
    
    private $panelId = '';
    private $file = array();

    function main(){
        $view = $_GET['view'];
        $view_filter = $this->get_views();
        $__filter = $view_filter[$view];
        if( $__filter['filter'] ) $filter = $__filter['filter'];
        
        $o = kernel::single('apiactionlog_finder_builder_panel_render',$this->finder_aliasname);
        $o->setFinder($this);
 
        $html = $o->main($this->object->table_name(), $this->app, $filter, $this->controller);
        
        $this->controller->pagedata['panel_html'] = $html;
    }

    public function setId($id) {
        $this->panelId = $id;
    }
    
    public function getId() {
        return $this->panelId;
    }
    
    public function setFile($file) {
        $this->file = $file;
    }
    
    public function getFile() {
        return $this->file;
    }
}
