<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2013 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_wap_search extends wap_frontpage{

     function __construct($app){
        parent::__construct($app);
        $shopname = app::get('site')->getConf('site.name');
        if(isset($shopname)){
            $this->title = app::get('wap')->_('搜索').'_'.$shopname;
            $this->keywords = app::get('wap')->_('搜索_').'_'.$shopname;
            $this->description = app::get('wap')->_('搜索_').'_'.$shopname;
        }
    }

    function index(){
        $aBrands = array();
        $objBrand = $this->app->model('brand');
        $this->pagedata['brand'] = $objBrand->getAll();
        $objCat = $this->app->model('goods_cat');
        $this->pagedata['categorys'] = $objCat->get_cat_list();
        $this->pagedata['args'] = array($cat_id,$filter,$orderBy,$tab,$page);
        //print_R($this->pagedata['args']);exit;
        $this->page('wap/search/index.html');
    }

    function result(){
      $this->set_no_store();
      $oSearch = $this->app->model('search');
      #$emu_static = $this->app->getConf('system.seo.emuStatic');
      foreach(kernel::servicelist("search.prepare") as $obj )
      {
        $obj->parse($_POST);
      }

      if($_POST['search_keywords']){
        $_POST['search_keywords']= str_replace('_','%xia%',$_POST['search_keywords']);
      }

      $filter = $oSearch->encode($_POST);
      $args = empty($filter) ? null : $filter;
      $url = $this->gen_url(array('app'=>'b2c', 'ctl'=>'wap_gallery', 'act'=>'index')).'?scontent='.$args;
      $this->_response->set_redirect($url)->send_headers();
    }

    public function associate(){
        $words = $_POST['value'];
        $searchrule = searchrule_search::instance('search_associate');
        if($searchrule && !empty($words)){
          $result = $searchrule->get_woreds($words);
          echo json_encode($result);exit;
        }else{
          echo '';exit;
        }
    }

}
