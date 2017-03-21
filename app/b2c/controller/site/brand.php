<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License */

class b2c_ctl_site_brand extends b2c_frontpage{
    var $seoTag=array('shopname','brand');

    function __construct($app){
        parent::__construct($app);
        $shopname = app::get('site')->getConf('site.name');
        $this->shopname = $shopname;
        $this->set_tmpl('brandlist');
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('品牌页').'_'.$shopname;
            $this->keywords = app::get('b2c')->_('品牌页').'_'.$shopname;
            $this->description = app::get('b2c')->_('品牌页').'_'.$shopname;
        }

    }

    public function showList($page=1){
        $pageLimit = 24;
        $oGoods=$this->app->model('brand');
        $result=$oGoods->getList('*', '',($page-1)*$pageLimit,$pageLimit,'ordernum desc');
        $brandCount = $oGoods->count();

        $oSearch = $this->app->model('search');

        $this->path[] = array('title'=>app::get('b2c')->_('品牌专区'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_brand', 'act'=>'showlist','full'=>1)));
        $GLOBALS['runtime']['path'] = $this->path;

        $title=$title['title']?$title['title']:app::get('b2c')->_('品牌');
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($brandCount/$pageLimit),
            'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_brand', 'act'=>'showList','full'=>1,'args'=>array(($tmp = time())))),
            'token'=>$tmp
            );

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $this->pagedata['data'] = $result;
        $this->setSeo('site_brand','showList',$this->prepareListSeoData($this->pagedata));
        $this->page('site/brand/showList.html');
    }

    public function index($brand_id, $page=1,$orderBy=1,$view='') {

        $brandModel=$this->app->model('brand');
        $argu=array("brand_id","brand_name","brand_url","brand_desc","brand_logo","brand_setting");
        $argu=implode(",",$argu);
        $result = $brandModel->getList($argu,array('brand_id'=>$brand_id));
        $result = $result[0];
        $this->set_tmpl('brand');
        if( $result['brand_setting']['brand_template'] ){
            $this->set_tmpl_file($result['brand_setting']['brand_template']);
        }
        $this->pagedata['brand_data'] = $result;

        $oSearch = $this->app->model('search');
        $params = $this->filter_decode($tmp_filter,$brand_id);
        $this->pagedata['filter'] = $params['params'];

		$page = $params['params']['page'] ? $params['params']['page'] : $page;
        $galleryController = kernel::single('b2c_ctl_site_gallery');
        $goodsData = $galleryController->get_goods($params['filter'],$page,$params['orderby']);
        $this->pagedata = array_merge($galleryController->pagedata,$this->pagedata);

        $screen = $this->brand_screen($brand_id);
        $this->pagedata['screen'] = $screen['screen'];
        $this->pagedata['showtype'] = $params['showtype'];
        $this->pagedata['orderby_sql'] = $params['orderby'];
        $this->pagedata['is_store'] = $params['is_store'];
        $this->pagedata['goodsData'] = $goodsData;

        //set 面包屑
        $this->path[] = array('title'=>app::get('b2c')->_('品牌专区'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_brand', 'act'=>'showlist','full'=>1)));
        $this->path[] = array('title'=>$result['brand_name'],'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $this->pagedata['link'] = $this->gen_url('gallery',$this->app->getConf('gallery.default_view'),array('',$oSearch->encode(array('brand_id'=>array($brand_id)))));
        $seo_info = $brandModel->dump($brand_id,'seo_info');
        if(!isset($seo_info['seo_info'])){
            $brandModel->brand_meta_register();
        }
        if(!empty($seo_info['seo_info']['seo_title']) || !empty($seo_info['seo_info']['seo_keywords']) || !empty($seo_info['seo_info']['seo_description'])){
            $this->title = $seo_info['seo_info']['seo_title'];
            $this->keywords = $seo_info['seo_info']['seo_keywords'];
            $this->description = $seo_info['seo_info']['seo_description'];
        }else{
            $this->setSeo('site_brand','index',$this->prepareSeoData($this->pagedata));
        }
        $this->pagedata['image_set'] = $imageDefault;
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $this->pagedata['gallery_display'] = $this->app->getConf('gallery.display.grid.colnum');
        if($count < $this->pagedata['gallery_display']){
            $this->pagedata['gwidth'] = $count * (100/$this->pagedata['gallery_display']);
        }else{
            $this->pagedata['gwidth'] = 100;
        }
        $this->page('site/brand/index.html');

    }

    function prepareSeoData($data){
        $intro = $this->get_brand_intro($data);
        return array(
            'shop_name'=>$this->shopname,
            'brand_name'=>$data['brand_data']['brand_name'],
            'brand_url'=>$data['brand_data']['brand_url'],
            'brand_intro'=>$intro,
            'goods_amount'=>$data['total']
        );
    }

    function prepareListSeoData($data){
    	if(is_array($data['data'])){
    	    foreach($data['data'] as $dk=>$dv){
    	    	if($dk == 0){
                    $brand_name = $dv['brand_name'];
    	    	}else{
    	    	    $brand_name .= ','.$dv['brand_name'];
    	    	}
    	    }
    	}
        return array(
            'shop_name'=>$this->shopname,
            'brand_name'=>$brand_name,
        );
    }

    private function brand_screen($brand_id){
        $screen['brand_id'] = $brand_id;

        //标签
        $tags = app::get('desktop')->model('tag')->getList('*');
        foreach($tags as $tag_key=>$tag_row){
            if($tag_row['tag_type'] == 'goods'){//商品标签
                if(in_array($tag_row['tag_id'],$filter['gTag'])){
                    $screen['tags']['goods'][$tag_key]['active'] = 'checked';
                }
                $screen['tags']['goods'][$tag_key]['tag_id'] = $tag_row['tag_id'];
                $screen['tags']['goods'][$tag_key]['tag_name'] = $tag_row['tag_name'];
            }
        }

        //排序
        $orderBy = $this->app->model('goods')->orderBy();
        $screen['orderBy'] = $orderBy;
        $return['screen'] = $screen;
        return $return;
    }

    //根据参数获取商品
    private function filter_decode($params=null,$brand_id){
        //获取cookie中的条件
        if(!$params){
            $cookie_filter = $_COOKIE['S']['BRAND']['FILTER'];
            if($cookie_filter){
                $tmp_params = explode('&',$cookie_filter);
                foreach($tmp_params as $k=>$v){
                    $arrfilter = explode('=',$v);
                    $f_k = str_replace('[]','',$arrfilter[0]);
                    $params[$f_k] = $arrfilter[1];
                }
            }
            if($params['brand_id'] != $brand_id){
                unset($params);
                $this->set_cookie('S[BRAND][FILTER]','nofilter');
            }
        }//end if

        $filter['params'] = $params;
        #排序
        $orderby = $params['orderBy'];unset($params['orderBy']);

        ##商品显示方式
        if($params['showtype']){
            $showtype = $params['showtype'];unset($params['showtype']);
        }else{
            $showtype = app::get('b2c')->getConf('gallery.default_view');
        }

        #是否有货
        $is_store = $params['is_store'];

        $tmp_filter['marketable'] = 'true';
        $tmp_filter['brand_id'] = $brand_id;
        #商品标签筛选条件
        if($tmp_filter['gTag']){
            $tmp_filter['tag'] = $tmp_filter['gTag'];unset($tmp_filter['gTag']);
        }

        $filter['filter'] = $tmp_filter;
        $filter['orderby'] = $orderby;
        $filter['showtype'] = $showtype;
        $filter['is_store'] = $is_store;
        return $filter;
    }

    private function get_brand_intro(&$result,$list=0){
        $brand_desc=preg_split('/(<[^<>]+>)/',$result['brand_data']['brand_desc'],-1);
        if(is_string($brand_desc)){
            if ( $brand_desc && strlen($brand_desc)>50)
                $brand_desc=substr($brand_desc,0,50);
        }
        return $brand_desc;
    }
}

