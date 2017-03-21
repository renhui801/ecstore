<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class site_ctl_sitemaps extends site_controller{




    public function index() {
    	$this->_response->set_header('Cache-Control', 'no-store');
        $_index = $this->_request->get_param(0);
        $o_sitemaps = base_kvstore::instance('site_sitemaps');
        $o_sitemaps->fetch( $_index, $arr );
        if( empty( $arr ) ) {
            kernel::single('site_router')->http_status(404);return;
        }else{
            $this->pagedata['sitemaps'] = $arr;
            $this->pagedata['base_url'] = (kernel::request()->get_port()==443)?str_replace('http', 'https', $this->app->res_url):$this->app->res_url;

            $this->_response->set_header('Content-type', ' application/xml');
            $this->page('sitemaps/index.xml', true);
        }
    }

    function catalog(){

        $o_sitemaps = base_kvstore::instance('site_sitemaps');
        $o_sitemaps->fetch( 'count', $count_sitemaps );
        $catalog = array();

        for( $i=1; $i<=$count_sitemaps; $i++ ) {
            $url = $this->gen_url( array('app'=>'site', 'ctl'=>'sitemaps', 'act'=>'index', 'arg0'=>$i, 'full'=>true ) );
            $catalog[]['url'] = $url;
        }
        $this->pagedata['base_url'] = kernel::base_url().'/app/site/view/sitemaps';
        $this->pagedata['catalog'] = $catalog;
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->_response->set_header('Content-type', ' application/xml');
        $this->page('sitemaps/catalog.xml', true);
    }

}
