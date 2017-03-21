<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_builder_packet extends desktop_finder_builder_prototype{

    function main(){

        $this->controller->pagedata['data'] = $this->get_views();
        /** 判断是否要显示归类视图 **/
        $this->controller->pagedata['haspacket'] = $this->controller->pagedata['data'] ? true : false;
        $this->controller->display('finder/view/packet.html','desktop');

    }

}
