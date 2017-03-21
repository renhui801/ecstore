<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_builder_export extends desktop_finder_builder_prototype{

    function main(){
        $render = app::get('desktop')->render();
        $ioType = array();
        foreach( kernel::servicelist('desktop_io') as $aio ){
            $ioType[] = $aio->io_type_name;
        }
        $render->pagedata['ioType'] = $ioType;
        if( $_GET['change_type'] )
            $render->pagedata['change_type'] = $_GET['change_type'];
        
        if( !$render->pagedata['thisUrl'] )
            $render->pagedata['thisUrl'] = $this->url;
        echo $render->fetch('common/export.html',app::get('desktop')->app_id);
    }
}
