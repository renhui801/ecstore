<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_site_product extends b2c_frontpage{

    function __construct($app){
        parent::__construct($app);
        $this->shopname = app::get('site')->getConf('site.name');
        if(isset($this->shopname)){
            $this->title = app::get('b2c')->_('商品页').'_'.$this->shopname;
            $this->keywords = app::get('b2c')->_('商品页').'_'.$this->shopname;
            $this->description = app::get('b2c')->_('商品页').'_'.$this->shopname;
        }

        $cur = app::get('ectools')->model('currency');
        //货币格式输出
        $ret = $cur->getFormat();
        $ret =array(
            'decimals'=>$this->app->getConf('system.money.decimals'),
            'dec_point'=>$this->app->getConf('system.money.dec_point'),
            'thousands_sep'=>$this->app->getConf('system.money.thousands_sep'),
            'fonttend_decimal_type'=>$this->app->getConf('system.money.operation.carryset'),
            'fonttend_decimal_remain'=>$this->app->getConf('system.money.decimals'),
            'sign' => $ret['sign']
        );
        $this->pagedata['money_format'] = json_encode($ret);
    }

    //获取商品详情页中的配置信息
    private function _get_goods_setting($goods_id){
        $setting['buytarget'] = $this->app->getConf('site.buy.target');//购物车弹出方式
        $setting['saveprice'] = $this->app->getConf('site.save_price');//商品页是否显示节省金额
        $setting['mktprice'] = $this->app->getConf('site.show_mark_price');//前台是否显示市场价
        $setting['member_price'] = $this->app->getConf('site.member_price_display');//前台是否显示会员价
        $setting['goodsbn'] = $this->app->getConf('goodsbn.display.switch');//是否启用商品编号
        $setting['goodsprop'] = $this->app->getConf('goodsprop.display.position');//属性显示位置
        $setting['show_order_sales'] = $this->app->getConf('goods.show_order_sales.type');//订单促销
        //是否开启评论咨询
        $setting['acomment']['switch']['ask'] = $this->app->getConf('comment.switch.ask');
        $setting['acomment']['switch']['discuss'] = $this->app->getConf('comment.switch.discuss');
        //是否开启评分
        $setting['acomment']['point_status']= app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $setting['recommend'] = $this->app->getConf('goods.recommend');//是否开启商品推荐
        $setting['isfastbuy'] = $this->app->getConf('site.isfastbuy_display');//是否显示立即购买
        $setting['scanbuy'] = app::get('wap')->getConf('wap.scanbuy');
        $setting['wap_status'] = app::get('wap')->getConf('wap.status');

        //是否显示销售记录
        $setting['selllog'] = 'false';
        $selllog_display = $this->app->getConf('selllog.display.switch');//是否显示销售记录
        if($selllog_display=='true'){
            $selllog_limit = $this->app->getConf('selllog.display.limit');
            $selllog_num = app::get('b2c')->model('products')->getGoodsSellLogNum($goods_id);
            if($selllog_num>=$selllog_limit){
                $setting['selllog'] = 'true';
            }
        }
        $setting['imageDefault']= app::get('image')->getConf('image.set');

        //判断是否是IE浏览器并且检查版本 （ajax选择货品的时候改变浏览器地址栏地址 IE不支持）
        if($_SERVER['HTTP_USER_AGENT'] && preg_match('/MSIE\s+([0-9.]+)/',$_SERVER['HTTP_USER_AGENT'],$matches)){
            $setting['Browser']['IE'] = true;
            $setting['Browser']['version'] = (int)$matches[1];
        }
        return $setting;
    }


    public function index() {

        $productsModel = $this->app->model('products');
        $goodsModel = $this->app->model('goods');

        //获取参数 货品ID
        $_getParams = $this->_request->get_params();
        $productId = (int)$_getParams[0];
		
		# 此商品是否参加starbuy活动
		if($object_price = kernel::service('special_goods')){
			$app_name = $object_price->ifSpecial($productId);
			$this->pagedata['app_name'] = $app_name?$app_name:"";
		}

        $userObject = kernel::single('b2c_user_object');
        $siteMember = $userObject->get_current_member();
        if( empty($siteMember['member_id']) ){
            $this->pagedata['login'] = 'nologin';
            $member_id = '-1';
        }else{
            $member_id = $siteMember['member_id'];
            $member_lv = $siteMember['member_lv'];
            $this->pagedata['member_info'] = $siteMember;
            $this->pagedata['this_member_lv_id'] = $member_lv;
        }

        $itemProduct = $productsModel->getList('*',array('product_id'=>$productId));
        if(!$itemProduct || $itemProduct === false ){
            kernel::single('site_router')->http_status(404);return;
            exit;
        }

        $goodsId = $itemProduct[0]['goods_id'];
        $aGoodsList = $goodsModel->getList('*',array('goods_id'=>$goodsId));
        if(!$aGoodsList || $aGoodsList === false || $aGoodsList[0]['goods_type'] != 'normal' ){
            kernel::single('site_router')->http_status(404);return;
            exit;
        }
        $aGoods = $aGoodsList[0];

        $aGoods['product'] = $itemProduct[0];

        //设置模板
        if( $aGoods['goods_setting']['goods_template'] ){
            $this->set_tmpl_file($aGoods['goods_setting']['goods_template']);                 //添加模板
        }
        $this->set_tmpl('product');


        //规格默认图片
        $this->pagedata['spec_default_pic'] = $this->app->getConf('spec.default.pic');

        $setting = $this->_get_goods_setting($goodsId);
        $this->pagedata['setting'] = $setting;
        //基本信息
        $productBasic = $this->_get_product_basic($productId,$aGoods,$siteMember);
        $this->pagedata['page_product_basic'] = $productBasic;

        $goodsAdjunct = $this->_get_goods_adjunct($aGoods);//配件信息
        $this->pagedata['page_goods_adjunct'] = $goodsAdjunct;

        //社会化分享
        $goodsshare = kernel::single('b2c_goods_share')->get_share($productBasic);
        $this->pagedata['goods_share'] = $goodsshare;

        /**** start 商品评分 ****/
        $objPoint = $this->app->model('comment_goods_point');
        $this->pagedata['goods_point'] = $objPoint->get_single_point($goodsId);
        $this->pagedata['total_point_nums'] = $objPoint->get_point_nums($goodsId);
        /**** end 商品评分 ****/

        $this->pagedata['btn_page_list'] = $this->_get_servicelist_by('b2c_products_index_btn');
        $this->pagedata['async_request_list'] = $this->get_body_async_url($productBasic);

        $GLOBALS['runtime']['path'] = $goodsModel->getPath($goodsId,'');

        $this->_set_seo($aGoods);

        // 商品详情页添加项埋点
        foreach( kernel::servicelist('goods_description_add_section') as $services ) {
            if ( is_object($services) ) {
                if ( method_exists($services, 'addSection') ) {
                    $services->addSection($this,$this->pagedata['goods']);
                }
            }
        }
        $this->page('site/product/index.html');
    }

    /*设置详情页SEO --start*/
    function _set_seo($aGoods){
        $seo_info = $aGoods['seo_info'];
        if(!empty($seo_info['seo_title']) || !empty($seo_info['seo_keywords']) || !empty($seo_info['seo_description'])){
            if( is_string($aGoods['seo_info']) ){
                $aGoods['seo_info'] = unserialize( $aGoods['seo_info'] );
            }
            if( $aGoods['seo_info']['seo_title'] ){
                $this->title = $aGoods['seo_info']['seo_title'];
            }
            if( $aGoods['seo_info']['seo_keywords'] ){
                $this->keywords = $aGoods['seo_info']['seo_keywords'];
            }
            if( $aGoods['seo_info']['seo_description'] ){
                $this->description = $aGoods['seo_info']['seo_description'];
            }
        }else{
            $this->setSeo('site_product','index',$this->prepareSeoData(array('goods'=>$aGoods)));
        }
    }

    function prepareSeoData($data){
        //商品简介
        $brief= strip_tags($data['goods']['brief']);
        if (strlen($brief)>50)
            $brief=substr($brief,0,50);

        //商品分类
        $pcat=$this->app->model('goods_cat');
        $cat_id = $data['goods']['cat_id'];
        if(!cachemgr::get('goods_cat'.intval($cat_id),$row)){
            cachemgr::co_start();
            $row=$pcat->getList("cat_name",array('cat_id'=>$data['goods']['cat_id']));
            cachemgr::set('goods_cat'.intval($cat_id), $row, cachemgr::co_end());
        }
        $goodsCat = $row[0]['cat_name'];
        return array(
            'goods_name'=>$data['goods']['name'],
            'goods_brand'=>$this->brand_name,
            'goods_bn'=>$data['goods']['bn'],
            'goods_cat'=>$goodsCat,
            'goods_brief'=>$brief,
            'goods_price'=>$data['goods']['product']['price']
        );
    }
    /*设置详情页SEO --end*/

    /*
     *返回servicelist
     *@param servicelist名称
     */
    private function _get_servicelist_by($servicelist)
    {
        if( !$servicelist ) return false;
        $list = array();
        foreach( kernel::servicelist($servicelist) as $object ) {
            if( !$object ) continue;
            $index = null;
            if( !$object->file ) continue; //模板文件 没有直接跳过
            if( method_exists($object,'get_order') )
                $index = $object->get_order();

            while(true) {
                if( !isset($list[$index]) ) break;
                $index++;
            }
            $path = explode('_',get_class($object));


            $list[$index] = array(
                                'file' => $object->file,
                                'app'  => $object->_app ? $object->_app : $path[0],
                            );

            if( method_exists($object,'set_page_data') ) {
                $object->set_page_data($this->customer_template_id,$this);//设置html内容
            }

            if( $servicelist=='b2c_products_index_btn' ) {
                if( method_exists($object,'unique') ) {
                    if( $object->unique() ) {
                        $tmp = array_pop($list);
                        $list = array($tmp);break;
                    }
                }
            }

        }

        krsort($list);
        return $list;
    }


    /*
     *商品详情页TAB添加services
     * */
    private function get_body_async_url($aGoods) {
        foreach($aGoods['type_tab'] as $key=>$list_row){
            $list['type_tab_'.$key]['name'] = $list_row['name'];
            //$list['type_tab_'.$key]['is_call'] = 'ajax';
            //$list['type_tab_'.$key]['class'] = 'this';
            $list['type_tab_'.$key]['is_call'] = $list_row['display'];
            $list['type_tab_'.$key]['content'] = $list_row['content'];
            $list['type_tab_'.$key]['view'] = 'site/product/tab/coustom_tab.html';
        }
        foreach( kernel::servicelist("b2c_product_index_async") as $object ) {
            if( !$object ) continue;
            $index = null;
            if( !method_exists($object,'getAsyncInfo') ) {
                continue;
            }

            if( method_exists($object,'get_order') )
                $index = $object->get_order();

            while(true) {
                if( !isset($list[$index]) ) break;
                $index++;
            }

            $asyncinfo = $object->getAsyncInfo($aGoods);
            if(!$asyncinfo) continue;
            $list[key($asyncinfo)] = ($asyncinfo[key($asyncinfo)]);

        }
        krsort($list);
        return $list;
    }

    /*---------------------TAB请求的方法----------------------------*/
    /*
     *获取商品详细参数数据
     * */
    function goodsParams($gid){
        $objGoods = $this->app->model('goods');
        $aGoods_list = $objGoods->getList("goods_id,type_id,params",array('goods_id'=>$gid));
        $params = $this->_get_goods_params($aGoods_list['0']['params']);
        $this->pagedata['goods_params'] = $params;
        echo $this->fetch('site/product/tab/params.html');
    }
    //评论
    function goodsDiscuss($gid){
        $this->pagedata['comments']= kernel::single("b2c_goods_description_comments")->show($gid,'discuss',10);
        $this->pagedata['pager'] = array(
            'current'=> $this->pagedata['comments']['discusscurrent'],
            'total'=> $this->pagedata['comments']['discusstotalpage'],
            'link'=>  $this->gen_url( array('app'=>'b2c','ctl'=>'site_comment',
            'act'=>'ajax_discuss','args'=>array($gid,($tmp = time())))),
            'token'=>$tmp
        );
        $this->pagedata['goods_id'] = $gid;
        $this->pagedata['page_type'] = 'tab';
        echo $this->fetch('site/product/tab/discuss.html');
    }

    //商品详情-评论
    function goodsDiscussInit($gid){
        $this->pagedata['comments']= kernel::single("b2c_goods_description_comments")->show($gid,'discuss',$limit);
        $this->pagedata['goods_id'] = $gid;
        echo $this->fetch('site/product/tab/discuss_init.html');
    }

    //咨询
    function goodsConsult($gid){
        $comments = kernel::single("b2c_goods_description_comments")->show($gid,'ask',10);
        $this->pagedata['comments'] = $comments;
        $this->pagedata['pager'] = array(
            'current'=> $this->pagedata['comments']['askcurrent'],
            'total'=> $this->pagedata['comments']['asktotalpage'],
            'link'=>  $this->gen_url( array('app'=>'b2c','ctl'=>'site_comment',
            'act'=>'ajax_ask','args'=>array($gid,'all',($tmp = time())))),
            'token'=>$tmp
        );
        $this->pagedata['goods_id'] = $gid;
        $this->pagedata['page_type'] = 'tab';
        echo $this->fetch('site/product/tab/ask.html');
    }

    //商品详情-咨询
    function goodsConsultInit($gid){
        $comments = kernel::single("b2c_goods_description_comments")->show($gid,'ask',10);
        $this->pagedata['comments'] = $comments;
        $this->pagedata['goods_id'] = $gid;
        echo $this->fetch('site/product/tab/ask_init.html');
    }

    //销售记录
    function goodsSellLoglist($gid,$nPage=0){
        $oPro = $this->app->model('products');
        $setting['selllog'] = 'false';
        $selllog_display = $this->app->getConf('selllog.display.switch');//是否显示销售记录
        if($selllog_display=='true'){
            $selllog_limit = $this->app->getConf('selllog.display.limit');
            $selllog_num = $oPro->getGoodsSellLogNum($gid);
            if($selllog_num>=$selllog_limit){
                $setting['selllog'] = 'true';
            }
        }
        if($setting['selllog'] == 'false'){
            echo '';exit;
        }
        $nPage = $nPage?$nPage:1;
        $sellLogList = $oPro->getGoodsSellLogList($gid, $nPage-1, app::get('b2c')->getConf('selllog.display.listnum'));
        $this->pagedata['sellLogList'] = $sellLogList;
        $this->pagedata['pager'] = array(
                'current'=> $nPage,
                'total'=> $sellLogList['page'],
                'link'=>  $this->gen_url( array('app'=>'b2c','ctl'=>'site_product',
                                'act'=>'goodsSellLoglist','args'=>array($gid,($tmp = time())))),
                'token'=>$tmp);
        echo $this->fetch('site/product/tab/selllog.html');
    }

    //获取相关商品
    function goodsLink($gid){
        $objGoods = $this->app->model("goods");
        $objProduct = $this->app->model("products");
        $aLinkId['goods_id'] = array();
        foreach($objGoods->getLinkList($gid) as $rows){
            if($rows['goods_1']==$gid){
                $aLinkId['goods_id'][] = $rows['goods_2'];
            }else {
                $aLinkId['goods_id'][] = $rows['goods_1'];
            }
        }
        if(count($aLinkId['goods_id'])>0){
            $aLinkId['marketable'] = 'true';
            $goodslink['link'] = $objGoods->getList('name,price,goods_id,image_default_id,marketable',$aLinkId,0,500);
            $products = $objProduct->getList('goods_id,product_id,is_default',$aLinkId,0,500);
            foreach ($products as $product_row){
                if($product_row['is_default'] == true){
                    $goodslink['products'][$product_row['goods_id']] = $product_row['product_id'];
                }else{
                    $goodslink['products'][$product_row['goods_id']] = $products[0]['product_id'];
                }
            }
        }
        $this->pagedata['setting']['buytarget'] = $this->app->getConf('site.buy.target');
        if(!$siteMember['member_id']){
            $this->pagedata['login'] = 'nologin';
        }
	    $this->pagedata['page_goodslink'] = $goodslink;
        echo $this->fetch('site/product/tab/goodslink.html');
    }


    /*-----------------商品详情页ajax调用模块 start------------*/
    /*
     *商品详情页价格显示
     * */
    function ajax_product_price($product_id){
        $this->_response->set_header('Cache-Control', 'no-store, no-cache');
        if(!cachemgr::get('ajax_product_price'.$product_id,$price)){
            cachemgr::co_start();
            $product = app::get('b2c')->model('products')->getList('*',array('product_id'=>$product_id));
            if(!$product){
                echo json_encode(array('error'=>app::get('b2c')->_('商品不存在')));
                return;
            }
            $aGoods = app::get('b2c')->model('goods')->getList('*',array('goods_id'=>$product[0]['goods_id']));
            $aGoods = $aGoods[0];
            $aGoods['product'] = $product[0];
            $price = $this->_get_product_price($product[0]['product_id'],$aGoods);

			#获取当前货品参加statbuy活动后的价格
			if($object_price = kernel::service('special_goods')){
				$object_price->getPrice($product_id,$price);
			}
            if($price['mlv_price']){
                $price['memberprice'] = $price['mlv_price'];
                unset($price['mlv_price']);
            }
            if($price['mktprice'] && $price['mktprice'] >$price['price']){
                $saveprice = $this->app->getConf('site.save_price');//商品页是否显示节省金额
                $objMath = kernel::single('ectools_math');
                if($saveprice == '1'){
                    $cur = app::get('ectools')->model('currency');
                    $ret = $cur->getFormat();
                    $price_saveprice = $objMath->number_minus(array($price['mktprice'],$price['price']));
                    if($price_saveprice > 0){
                      $price['saveprice'] = app::get('b2c')->_('(节省').$ret['sign'].$price_saveprice.')';
                    }else{
                      $price['saveprice'] = app::get('b2c')->_('(节省').'0)';
                   }
                }elseif($saveprice == '2'){
                    $price_saveprice = $objMath->number_multiple(array(100,$objMath->number_minus(array(1,$objMath->number_div(array($price['price'],$price['mktprice']))))));
                    $price['saveprice'] = app::get('b2c')->_('(优惠').$price_saveprice.'%)';
                }elseif($saveprice == '3'){
                    $price_saveprice = $objMath->number_multiple(array(10,$objMath->number_div(array($price['price'],$price['mktprice']))));
                    $price['saveprice'] = app::get('b2c')->_('(折扣').$price_saveprice.app::get('b2c')->_('折)');
                }
            }

            cachemgr::set('ajax_product_price'.$product_id, $price, cachemgr::co_end());
        }
        echo json_encode($price);
    }

    function ajax_product_store($product_id)
    {
        $this->_response->set_header('Cache-Control', 'no-store, no-cache');
        if(!cachemgr::get('ajax_product_store'.$product_id,$store)){
            cachemgr::co_start();
            $product = app::get('b2c')->model('products')->getList('*',array('product_id'=>$product_id));
            if(!$product){
                echo json_encode(array('error'=>app::get('b2c')->_('商品不存在')));
                return;
            }
            $store = $this->_get_product_store($product_id);
            cachemgr::set('ajax_product_store'.$product_id, $store, cachemgr::co_end());
        }
        echo json_encode($store);
    }

    /*商品详情页库存显示*/
    function _get_product_store($product_id){
        $product = app::get('b2c')->model('products')->getList('goods_id,store,freez',array('product_id'=>$product_id));
        if($product){
            $goodsdata = app::get('b2c')->model('goods')->getList('goods_id,nostore_sell,store,store_prompt',array('goods_id'=>$product[0]['goods_id']));
            if($goodsdata && ($goodsdata[0]['nostore_sell'] || $goodsdata[0]['store'] === null)){
                $store['store'] = 999999;//暂时表示库存无限大
            }else{
                $goodsStore = $product[0]['store'] - $product[0]['freez'];
                $store['store'] = ($goodsStore >= 0)? $goodsStore : 0 ;
            }
            $show_storage = app::get('b2c')->getConf('site.show_storage');
            switch($show_storage){
                case '1';//不显示库存提示
                    $store['title'] = null;
                    break;
                case '2';//显示库存数量提示
                    $store['title'] =  $store['store'];
                    break;
                case '3';//启用库存优化方案
                    if($goodsdata[0]['store_prompt']){
                        $store_prompt = app::get('b2c')->model('goods_store_prompt')->getList('`values`',array('prompt_id'=>$goodsdata[0]['store_prompt']));
                    }
                    if($store_prompt){
                        $values = unserialize($store_prompt[0]['values']);
                        foreach($values as $params){
                            if($store['store'] >= $params['min'] && $store['store'] < $params['max']){
                                $store['title'] = $params['title']; break;
                            }
                        }
                    }
                    break;
            }

			# 获取starbuy限购数量
			if($object_price = kernel::service('special_goods')){
				$object_price->getStore($product_id,$store);
			}

            $this->pagedata['product_store'] = $store;
            return $store;
        }
    }

    //货品基本信息
    function ajax_product_basic($product_id){
        $this->_response->set_header('Cache-Control', 'no-store, no-cache');
        if(!cachemgr::get('ajax_product_basic'.$product_id,$basic_html)){
            cachemgr::co_start();
            $product = app::get('b2c')->model('products')->getList('*',array('product_id'=>$product_id));
            if(!$product){
                echo json_encode(array('error'=>app::get('b2c')->_('商品不存在')));
                return;
            }

			if($object_price = kernel::service('special_goods')){
				$app_name = $object_price->ifSpecial($product_id);
				$this->pagedata['app_name'] = $app_name?$app_name:"";
			}
				
            $aGoods = app::get('b2c')->model('goods')->getList('*',array('goods_id'=>$product[0]['goods_id']));
            $aGoods = $aGoods[0];
            $aGoods['product'] = $product[0];
            $userObject = kernel::single('b2c_user_object');
            $siteMember = $userObject->get_current_member();
            if( empty($siteMember['member_id']) ){
                $this->pagedata['login'] = 'nologin';
                $member_id = '-1';
            }else{
                $member_id = $siteMember['member_id'];
                $member_lv = $siteMember['member_lv'];
                $this->pagedata['member_info'] = $siteMember;
                $this->pagedata['this_member_lv_id'] = $member_lv;
            }
            $goodsId = $aGoods['goods_id'];
            $setting = $this->_get_goods_setting($goodsId);
            $this->pagedata['setting'] = $setting;
            $productBasic = $this->_get_product_basic($product_id,$aGoods,$siteMember);
            $gfav = explode(',',$_COOKIE['S']['GFAV'][$siteMember['member_id']]);
            if(in_array($aGoods['goods_id'], $gfav)){
                $productBasic['is_fav'] = true;
            }
            //btn
            $this->pagedata['btn_page_list'] = $this->_get_servicelist_by('b2c_products_index_btn');
            $this->pagedata['page_product_basic'] = $productBasic;

            //社会化分享
            $goodsshare = kernel::single('b2c_goods_share')->get_share($productBasic);
            $this->pagedata['goods_share'] = $goodsshare;

            /**** start 商品评分 ****/
            $objPoint = $this->app->model('comment_goods_point');
            $this->pagedata['goods_point'] = $objPoint->get_single_point($goodsId);
            $this->pagedata['total_point_nums'] = $objPoint->get_point_nums($goodsId);
            /**** end 商品评分 ****/

            $basic_html = $this->fetch('site/product/basic.html');
            cachemgr::set('ajax_product_basic'.$product_id, $basic_html, cachemgr::co_end());
        }
        echo $basic_html;
    }

    /*-----------------ajax调用模块 end------------*/


    /*-----------------以下为商品详情页各模块数据处理------------*/

    /*
     *商品详情参数数据处理
     * */
    private function _get_goods_params($params){
        if(!empty($params)){
            foreach($params as $key=>$row){
                $row = array_filter($row);
                if(empty($row)){
                    unset($params[$key]);
                }
            }
        }
        return $params;
    }
    //价格数据处理
    function _get_product_price($productId,$aGoods,$member_lv){
        $goodsPrice = array();
        $objCurrency = app::get('ectools')->model('currency');
        $money_format = json_decode($this->pagedata['money_format'],true);
        $productMemberPrice['price'] = $objCurrency->changer_odr($aGoods['product']['price'], $_COOKIE['S']['CUR'], true, false, $money_format['decimals'], $money_format['fonttend_decimal_type']);
        $productsModel = $this->app->model("products");
        //市场价
        $setting_mktprice = $this->app->getConf('site.show_mark_price');//前台是否显示市场价
        if($setting_mktprice  == 'true'){
            if( $aGoods['product']['mktprice'] == '' || $aGoods['product']['mktprice'] == null ){
                $mktprice = $aGoods['mktprice'];
            }else{
                $mktprice = $aGoods['product']['mktprice'];
            }
            if( $mktprice == '' || $mktprice == null ){
                $productMemberPrice['mktprice'] = $productsModel->getRealMkt($aGoods['price']);
            }else{
                $productMemberPrice['mktprice'] = $mktprice;
            }
            $productMemberPrice['mktprice'] = $objCurrency->changer_odr($productMemberPrice['mktprice'], $_COOKIE['S']['CUR'], true, false, $money_format['decimals'], $money_format['fonttend_decimal_type']);
        }


        $setting_member_price = $this->app->getConf('site.member_price_display');//前台是否显示会员价
        if($setting_member_price == '1'){
            //会员价
            $memberLv = app::get('b2c')->model('member_lv')->getList('member_lv_id,name,dis_count');
            $customMemberPrice = app::get('b2c')->model('goods_lv_price')->getList('*',array('product_id'=>$productId));
            if(!empty($customMemberPrice) ){
                foreach($customMemberPrice as $value){
                    $tempCustom[$value['level_id']] = $value;
                }
            }
            $minPrice = null;
            $i = 0;
            foreach($memberLv as $memberValue){
                if( !empty($tempCustom[$memberValue['member_lv_id']]) ){
                    $productMemberPrice['mlv_price'][$i]['name'] = $memberValue['name'];
                    $productMemberPrice['mlv_price'][$i]['price'] = $tempCustom[$memberValue['member_lv_id']]['price'];
                    $productMemberPrice['mlv_price'][$i]['price'] = $objCurrency->changer_odr($productMemberPrice['mlv_price'][$i]['price'], $_COOKIE['S']['CUR'], true, false, $money_format['decimals'], $money_format['fonttend_decimal_type']);
                }else{
                    $productMemberPrice['mlv_price'][$i]['name'] = $memberValue['name'];
                    $productMemberPrice['mlv_price'][$i]['price'] = $aGoods['product']['price'] * $memberValue['dis_count'];
                    $productMemberPrice['mlv_price'][$i]['price'] = $objCurrency->changer_odr($productMemberPrice['mlv_price'][$i]['price'], $_COOKIE['S']['CUR'], true, false, $money_format['decimals'], $money_format['fonttend_decimal_type']);
                    if($memberValue['member_lv_id'] == $member_lv){
                        $productMemberPrice['price'] = $productMemberPrice['mlv_price'][$i]['price'];
                    }
                }
                if($minPrice === null ){
                    $minPrice = $productMemberPrice['mlv_price'][$i]['price'];
                }else{
                    if($minPrice >= $productMemberPrice['mlv_price'][$i]['price']){
                        $minPrice = $productMemberPrice['mlv_price'][$i]['price'];
                    }
                }
                $i++;
            }
        }//end
        $productMemberPrice['minprice'] = $minPrice;
        return $productMemberPrice;
    }

    //货品基本数据
    function _get_product_basic($productId,$aGoods,$siteMember){
        $goodsObject = kernel::single('b2c_goods_object');
        $productBasic= array();
        $productBasic['goods_id'] = $aGoods['goods_id'];
        $productBasic['product_id'] = $aGoods['product']['product_id'];
        $productBasic['product_bn'] = $aGoods['product']['bn'];
        $productBasic['qrcode_image_id'] = $aGoods['product']['qrcode_image_id'];
        $productBasic['price'] = $aGoods['price'];
        $productBasic['intro'] = $aGoods['intro'];
        $productBasic['unit'] = $aGoods['unit'];
        $productBasic['title'] = $aGoods['name'];//主标题
        $productBasic['brief'] = $aGoods['brief'];//副标题
        $productBasic['product_marketable'] = $aGoods['product']['marketable'];//是否上架
        $productBasic['goods_marketable'] = $aGoods['marketable'];//是否上架
        $productBasic['nostore_sell'] = $aGoods['nostore_sell'];//是否开启无库存销售

        $goodsBasic = $goodsObject->get_goods_basic($aGoods['goods_id'],$aGoods);
        if($goodsBasic['type']['setting']['use_params']){
            $productBasic['params'] = $this->_get_goods_params($aGoods['params']);
        }
        $productBasic['type_name'] = $goodsBasic['type']['name'];//商品类型名称
        $productBasic['cat_name'] = $goodsBasic['category']['cat_name'];//商品分类名称
        $productBasic['brand']['brand_name'] = $goodsBasic['brand']['brand_name'];//商品品牌名称
        $this->brand_name = $goodsBasic['brand']['brand_name'];//seo 品牌名称
        $productBasic['brand']['brand_id'] = $goodsBasic['brand']['brand_id'];//商品品牌ID

        //促销
        if(empty($siteMember['member_lv'])){
            $siteMember['member_lv'] = '-1';
        }
        $productPromotion= $this->_get_goods_promotion($aGoods['goods_id'],$aGoods,$siteMember['member_lv']);
        $productBasic['promotion'] = $productPromotion;//商品促销

		#此商品是否参加starbuy活动
		$object_price = kernel::service('special_goods');
		if($object_price && $object_price->ifSpecial($productBasic['product_id'])){
			foreach($productBasic['promotion'] as $k=>$v){
				if($k != "order"){ unset($productBasic['promotion'][$k]);}
			}
		}

        //扩展属性
        $goodsProps = $this->_get_goods_props($goodsBasic['type']['props'],$aGoods);
        $productBasic['props'] = $goodsProps;//商品类型中扩展属性

        //规格
        $goodsSpec = $this->_get_goods_spec($aGoods);
        $productBasic['spec'] = $goodsSpec;//商品规格

        #货品价格 货品库存 ajax调用

        #//货品类型的自定义tab
        $goodsTypeTab = $this->_get_goods_type_tab($goodsBasic['type']);
        $productBasic['type_tab'] = $goodsTypeTab;

        //没有默认货品图片则显示商品所有图片，否则显示关联货品图片
        $default_spec_image = $this->app->getConf('spec.default.pic');
        foreach($goodsSpec['product'] as $k=>$row){
            $spec_goods_images = $goodsSpec['goods'][$k][$row]['spec_goods_images'];
            if(!empty($spec_goods_images) && $spec_goods_images != $default_spec_image){
                $imagesArr = explode(',',$spec_goods_images);
                foreach( (array)$imagesArr as $image_id ){
                    $productBasic['images'][]['image_id'] = $image_id;
                }
            }
        }
        if(empty($productBasic['images'])){
            $goodsImages = $this->_get_goods_image($aGoods);
            $productBasic['images'] = $goodsImages;//商品图片
            $productBasic['image_default_id'] = $aGoods['image_default_id'];//商品图片
        }else{
            $productBasic['image_default_id'] = $productBasic['images'][0]['image_id'];//商品图片
        }

        return $productBasic;
    }

    function _get_goods_type_tab($type){
        if($type['setting']['use_tab']){
            return $type['tab'];
        }else{
            return array();
        }
    }

    /*
     *获取商品图片数据
     * */
    function _get_goods_image($aGoods){
        $image = app::get("image")->model("image_attach");
        $image_data = $image->getList("attach_id,image_id",array("target_id"=>intval($aGoods['goods_id']),'target_type'=>'goods'));
        foreach($image_data as $img_k=>$img_v){
            $goodsImages[$img_v['attach_id']] = $img_v;
        }
        return $goodsImages;
    }

    //获取货品规格数据
    function _get_goods_spec($aGoods){
        $goodsSpec = array();
        $products = app::get('b2c')->model('products')->getList('product_id,spec_desc,store,freez,marketable',array('goods_id'=>$aGoods['goods_id']));
        if($aGoods['spec_desc']){
            $goodsSpec['goods'] = $aGoods['spec_desc'];
            $goodsSpec['product'] = $aGoods['product']['spec_desc']['spec_private_value_id'];
            foreach($products as $row){
                $products_spec = $row['spec_desc']['spec_private_value_id'];
                $diff_class = array_diff_assoc($products_spec,$goodsSpec['product']);//求出当前货品和其他货品规格的差集
                if(count($diff_class) === 1){
                    $goodsSpec['goods'][key($diff_class)][current($diff_class)]['product_id'] = $row['product_id'];
                    $goodsSpec['goods'][key($diff_class)][current($diff_class)]['marketable'] = $row['marketable'];
                    if($row['store'] === '' || $row['store'] === null){
                        $product_store = '999999';
                    }else{
                        $product_store = $row['store']-$row['freez'];
                    }
                    $goodsSpec['goods'][key($diff_class)][current($diff_class)]['store'] = $product_store;
                }
            }

            foreach($aGoods['spec_desc'] as $specId=>$specValue){
                $arrSpecId['spec_id'][] = $specId;
            }

            $arrSpecName = app::get('b2c')->model('specification')->getList('spec_name,spec_id,spec_type',$arrSpecId,0,-1,'p_order ASC');
            foreach($arrSpecName as $specItem){
                $goodsSpec['specification']['spec_name'][$specItem['spec_id']] = $specItem['spec_name'];
                $goodsSpec['specification']['spec_type'][$specItem['spec_id']] = $specItem['spec_type'];

            }
        }
        return $goodsSpec;
    }

    /*
     *获取商品配件数据
     * */
    function _get_goods_adjunct($aGoods){
        $gid = $aGoods['goods_id'];
        //反序列化商品配件信息
        if(!is_array($aGoods['adjunct'])){
            $goodsAdjunct = unserialize($aGoods['adjunct']);
        }else{
            $goodsAdjunct = $aGoods['adjunct'];
        }

        if(count($goodsAdjunct) > 0){
            foreach($goodsAdjunct as $key => $rows){    //loop group
                #if($rows['price'] >= '1'){
                #    $cols = 'product_id,goods_id,name, spec_info, store, freez, price, price-'.intval($rows['price']).' AS adjprice,marketable';
                #}else{
                #    $cols = 'product_id,goods_id,name, spec_info,store, freez, price, price*'.($rows['price']?$rows['price']:1).' AS adjprice,marketable';
                #}
                $cols = 'product_id,goods_id,name, spec_info, store, freez, price,marketable';

                if($rows['type'] == 'goods'){
                    if(!$rows['items']['product_id']) $rows['items']['product_id'] = array(-1);
                    $arr = $rows['items'];
                }else{
                    parse_str($rows['items'].'&dis_goods[]='.$gid, $arr);
                }
                $gfilter = array('marketable'=>'true');
                if(isset($arr['type_id'])){
                    if(is_array($arr['props'])){
                        $c = 1;
                        foreach($arr['props'] as $pk=>$pv){
                            $p_id= 'p_'.$c;
                             foreach($pv as $sv){
                                 if($sv == '_ANY_'){
                                     unset($pv);
                                 }
                             }
                             if(isset($pv))
                                 $arr[$p_id] = $pv;
                             $c++;
                        }
                        unset($arr['props']);
                    }

                    if($arr){
                        $gId = app::get('b2c')->model('goods')->getList('goods_id',$arr,0,-1);
                    }
                    if(is_array($gId)){
                        foreach($gId as $gv){
                            $gfilter['goods_id'][] = $gv['goods_id'];
                        }
                        if(empty($gfilter))
                        $gfilter['goods_id'] = '-1';
                    }
                }else{
                    $gfilter = $arr;
                }
                if($aAdj = $this->app->model('products')->getList($cols,$gfilter,0,-1)){
                    foreach($aAdj as $k=>$aAdj_row){
                        if($rows['set_price'] == 'minus'){
                            $adjprice = $aAdj_row['price'] - intval($rows['price']);
                            $aAdj[$k]['adjprice'] = ($adjprice > 0) ? $adjprice : 0;
                        }else{
                            $aAdj[$k]['adjprice'] = $aAdj_row['price'] * ($rows['price']?$rows['price']:1);
                        }
                        $goods_ids['goods_id'][] = $aAdj_row['goods_id'];
                        $store = $aAdj_row['store']-$aAdj_row['freez'];
                        if(is_null($aAdj_row['store'])){
                            $aAdj_row['store'] = 999999;
                            $store = 999999;
                        }
                        if( ($rows['min_num'] && $store < $rows['min_num']) || $store <= 0){
                            unset($aAdj[$k]);
                        }
                    }
                    if($aAdj){
                        $goodsAdjunct[$key]['items'] = $aAdj;
                    }else{
                        unset($goodsAdjunct[$key]);
                    }
                }else{
                    unset($goodsAdjunct[$key]);
                }
            }
             //构造配件商品默认图片数据
            if(!empty($goods_ids)){
                $adjGoodsInfo = app::get('b2c')->model('goods')->getList('goods_id,image_default_id',$goods_ids);
                foreach($adjGoodsInfo  as $adjGoodsInfo_value){
                    $adjunct_images[$adjGoodsInfo_value['goods_id']] = $adjGoodsInfo_value['image_default_id'];
                }
                $this->pagedata['adjunct_images'] = $adjunct_images;
            }
        }
        return $goodsAdjunct;
    }

    function _get_goods_props($arrProps,$aGoods){
        if( empty($arrProps) ){
            return null;
        }
        $goodsProps = array();
        for ($i=1;$i<=50;$i++){
            //1-20 select 21-50 input
            if ($aGoods['p_'.$i] ){
                $propsValueId = $aGoods['p_'.$i];
                $k = $arrProps[$i]['ordernum'].'_'.$i;
                if( $i <= 20){
                    $goodsProps[$k]['name'] = $arrProps[$i]['name'];
                    $goodsProps[$k]['value'] = $arrProps[$i]['options'][$propsValueId];
                }else{
                    $goodsProps[$k]['name'] = $arrProps[$i]['name'];
                    $goodsProps[$k]['value'] = $propsValueId;
                }

                //如果商品类型扩展属性改变，则商品中的设置需要重现设置，原先设置无效
                if(empty($goodsProps[$k]['name']) || empty($goodsProps[$k]['value']) ){
                    unset($goodsProps[$k]);
                    continue;
                }
            }
        }
        ksort($goodsProps);
        return $goodsProps;
    }

    /*
     *获取商品促销数据
     * */
    function _get_goods_promotion($goodsId,$aGoods,$member_lv_id){
        $goodsPromotion = kernel::single('b2c_goods_object')->get_goods_promotion($goodsId);
        $productPromotion = array();
        $giftId = array();
        //商品促销
        foreach($goodsPromotion['goods'] as $row){
            $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
            if(key($temp) == 'gift_promotion_solutions_gift'){
                if(strpos($row['member_lv_ids'],$member_lv_id) === false){
                    continue;
                }
                $giftId = array_merge($giftId,$temp['gift_promotion_solutions_gift']['gain_gift']);
                continue;
            }
            
            if(isset($same_rule[key($temp)]) && $same_rule[key($temp)]){
                continue;
            }else{
                $same_rule[key($temp)] = true;
            }
            $ruleData = app::get('b2c')->model('sales_rule_goods')->getList('name',array('rule_id'=>$row['rule_id']));
            $productPromotion['goods'][$row['rule_id']]['name'] = $ruleData[0]['name'];
            $productTag = kernel::single(key($temp))->get_desc_tag();
            $productPromotion['goods'][$row['rule_id']]['tag'] = $productTag['name'];
            if(strpos($row['member_lv_ids'],$member_lv_id) === false){
                $productPromotion['goods'][$row['rule_id']]['use'] = 'false';
            }else{
                $productPromotion['goods'][$row['rule_id']]['use'] = 'true';
            }
        }

        //订单促销
        $giftCartObject = kernel::single('gift_cart_object_goods');
        foreach($goodsPromotion['order'] as $row){
            $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
            if(key($temp) == 'gift_promotion_solutions_gift'){
                $gain_gift = $temp['gift_promotion_solutions_gift']['gain_gift'];
                $giftId = array_merge($giftId,$gain_gift);
                if(!$giftCartObject->check_gift($giftId)){
                    continue;
                }
            }
            $productTag = kernel::single(key($row['action_solution']))->get_desc_tag();
            $productPromotion['order'][$row['rule_id']]['name'] = $row['name'];
            $productPromotion['order'][$row['rule_id']]['tag'] = $productTag['name'];
        }
        //赠品
        if($giftId){
            $giftRef = app::get('gift')->model('ref')->getList('*',array('product_id'=>$giftId,'marketable'=>'true'));
            if($giftRef){
                foreach($giftRef as $key=>$row){
                    if($row['marketable'] == 'false') continue;
                    if($row['cat_id']){
                        $giftCat = app::get('gift')->model('cat')->getList('*',array('cat_id'=>$row['cat_id']));
                        if($giftCat[0]['ifpub'] == 'false') continue;
                    }
                    $newGiftId[] = $row['product_id'];
                }
            }

            $aGift = app::get('b2c')->model('products')->getList('goods_id,product_id,name,store,freez',array('product_id'=>$newGiftId,'marketable='=>'true') );
            foreach($aGift as $key=>$row){
                $arrGoodsId[$key] = $row['goods_id'];
                if(is_null($row['store'])){
                    $aGift[$key]['store'] = 999999;
                }
            }
            $image = app::get('b2c')->model('goods')->getList('image_default_id,goods_id,nostore_sell,marketable',array('goods_id'=>$arrGoodsId) );
            $gift_image = array();
            foreach($image as $v){
                $gift_image[$v['goods_id']]=$v;
            }

            foreach($aGift as $key=>&$row){
                if($gift_image[$row['goods_id']]['marketable'] == 'false'){
                    unset($aGift[$key]);continue;
                }
                $row['image_default_id'] = $gift_image[$row['goods_id']]['image_default_id'];
                if($gift_image[$row['goods_id']]['nostore_sell']){
                    $row['store'] = 999999;
                }

            }
            $productPromotion['gift'] = $aGift;
        }
        return $productPromotion;
    }


    //商品相册
    function albums($goodsid, $selected='def'){
        $objGoods = $this->app->model('goods');
        $o = app::get('image')->model('image_attach');
        $dImg = $o->getList('*',array('target_id'=>$goodsid,'target_type'=>'goods'));
        $thumbnail_pic = $objGoods->getList('thumbnail_pic',array('goods_id'=>$goodsid));
        $aGoods = $objGoods->dump($goodsid,'name');
        $this->pagedata['goods_name'] = urlencode(htmlspecialchars($aGoods['name'],ENT_QUOTES));
        $this->pagedata['goods_name_show'] = $aGoods['name'];
        $this->pagedata['company_name'] = str_replace("'","&apos;",htmlspecialchars(app::get('site')->getConf('site.name')));
        if(!empty($thumbnail_pic[0]['thumbnail_pic'])){
            $dImg[]['image_id'] = $thumbnail_pic[0]['thumbnail_pic'];
        }
        if(!$dImg){
            $imageDefault = app::get('image')->getConf('image.set');
            $dImg[]['image_id'] = $imageDefault['L']['image_id'];
        }
        if(is_array($dImg)){
            foreach($dImg as $dk=>$dv){
                $json_image[] = '\''.base_storager::image_path($dv['image_id'],'l').'\'';
            }
        }
        $this->pagedata['image_file'] = $dImg;
        $this->pagedata['image_file_total'] = count($dImg);
        if(count($json_image>0)){
            $this->pagedata['json_image'] = implode(',',$json_image);
        }

        if($selected=='def'){
            $selected=current($dImg);
            $selected=$selected['target_id'];
        }
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        $this->pagedata['selected'] = $selected;
        $this->pagedata['goods_id'] = $goodsid;
        $shop['url']['shipping'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'shipping'));
        $shop['url']['total'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'total'));
        $shop['url']['region'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_tools','act'=>'selRegion'));
        $shop['url']['payment'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_cart','act'=>'payment'));
        $shop['url']['diff'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','act'=>'diff'));
        if(defined('DEBUG_JS') && constant('DEBUG_JS')){
            $path = 'js';
        }
        else {
            $path = 'js_mini';
        }
        $shop['url']['datepicker'] = app::get('site')->res_url.'/'.$path;
        $shop['url']['placeholder'] = app::get('b2c')->res_url.'/images/imglazyload.gif';
        $shop['base_url'] = kernel::base_url().'/';
        $this->pagedata['shopDefine'] = json_encode($shop);

        $this->page('site/product/albums.html',true);

    }

    //商品推荐-发送邮件
    function recommend($goods_id,$product_id){
        $email_array = explode(',',$_POST['email']);
        if(empty($email_array) || count($email_array) > 5){
            echo json_encode(array('error'=>app::get('b2c')->_('推荐最多5位，最少1位')));
            exit;
        }
        $goodsdata = app::get('b2c')->model('goods')->getList('name,brief',array('goods_id'=>$goods_id));
        if(empty($goodsdata)){
            echo json_encode(array('error'=>app::get('b2c')->_('参数错误')));
            exit;
        }
        $userObject = kernel::single('b2c_user_object');
        $siteMember = $userObject->get_current_member();
        if(empty($siteMember['member_id'])){
            echo json_encode(array('error'=>app::get('b2c')->_('未登录不能发表推荐')));
            exit;
        }
        //获取当前货品的URL地址
        $url = kernel::single('base_component_request')->get_full_http_host().kernel::single('site_controller')->gen_url(array('app'=>'b2c','ctl'=>'site_product','arg0'=>$product_id));
        $data['uname'] = $siteMember['uname'];
        $data['content'] = $_POST['content']?$_POST['content'] : '';
        $data['shopname'] = $this->shopname;
        $data['goods_brief'] = $goodsdata[0]['brief'];
        $data['goods_name'] = $goodsdata[0]['name'];
        $data['goods_url'] = $url;
        foreach($email_array as $email){
            app::get('b2c')->model('member_messenger')->actionSend('goods-recommend',$data,null,$email);
        }
        echo json_encode(array('success'=>app::get('b2c')->_('邮件发送成功')));
    }

    //缺货登记
    function toNotify(){
        if(!$_POST['item'][0]['goods_id'] || !$_POST['item'][0]['product_id']){
            $this->splash('failed', null, app::get('b2c')->_('参数错误'),true);
        }
        $back_url = null;//$this->gen_url(array('app'=>'b2c','ctl'=>'site_product','arg0'=>$_POST['item'][0]['product_id']));
        if (empty($_POST['email']) && empty($_POST['cellphone'])) {
            $this->splash('failed', $back_url, app::get('b2c')->_('邮箱或手机号请至少填一项'),true);
        }
        if(!empty($_POST['email']) && !preg_match('/^(?:[a-z\d]+[_\-\+\.]?)*[a-z\d]+@(?:([a-z\d]+\-?)*[a-z\d]+\.)+([a-z]{2,})+$/i', $_POST['email'])){
            $this->splash('failed', $back_url, app::get('b2c')->_('邮箱格式错误'),true);
        }
        if(empty($_POST['cellphone'])){
            $this->splash('failed', $back_url, app::get('b2c')->_('手机格式错误'),true);
        }
        $objGoods = $this->app->Model('goods');
        $objProducts = $this->app->Model('products');
        $ret = $objProducts->getList('product_id',array('product_id' => $_POST['item'][0]['product_id'],'goods_id' => $_POST['item'][0]['goods_id']));
        if(!$ret) $this->splash('failed', $back_url, app::get('b2c')->_('参数错误'),true);
        $member_goods = $this->app->model('member_goods');
        if($member_goods->check_gnotify($_POST)){
            $this->splash('failed',$back_url,app::get('b2c')->_('不能重复登记'),true);
        }else{
            $userObject = kernel::single('b2c_user_object');
            $member_id = $userObject->get_member_id();
            if($member_goods->add_gnotify($member_id?$member_id:null,$_POST['item'][0]['goods_id'],$_POST['item'][0]['product_id'],$_POST['email'],$_POST['cellphone'])){
                $objGoods->db->exec("update sdb_b2c_goods set notify_num=notify_num+1 where goods_id = ".intval($_POST['item'][0]['goods_id']));
                $this->splash('true',null,app::get('b2c')->_('登记成功'),true);
            }else{
                $this->splash('failed',$back_url,app::get('b2c')->_('登记失败'),true);
            }
        }
    }


    public function cron($goods_id){
        $this->_response->set_header('Cache-Control', 'no-store, no-cache');
        kernel::single('b2c_goods_crontab')->run($goods_id);
    }

    /*
     * 当浏览历史记录取不到图片时,发送请求获取默认图片
       @author litie@shopex.cn
       $gids like:  2,3,4,5,6,7
       @return like:
       [{"goods_id":"39","thumbnail_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/77900fbf8fcc94de.jpg","small_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/4d927b00ab29b199.jpg","big_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/389e97389f1616f7.jpg"},{"goods_id":"42","thumbnail_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/54d1c53bc455244f.jpg","small_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/9dce731f131aab5e.jpg","big_pic":"http:\/\/pic.shopex.cn\/pictures\/gimages\/ac4420118e680927.jpg"}]
    */
    function picsJson(){
        $gids = explode(',',$_GET['gids']);

        if(!$gids)return '';
        $o = $this->app->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');

        $data = $o->db_dump(current($gids),'image_default_id');
        if( !$data['image_default_id'] ){
            $data = base_storager::image_path( $imageDefault['S']['default_image'],'s' );
        }else{
            $img = base_storager::image_path($data['image_default_id'],'s' );
            if( $img )
                $data = $img;
            else
                $data = base_storager::image_path( $imageDefault['S']['default_image'],'s' );
        }
        echo json_encode($data);
        exit;
    }

}
