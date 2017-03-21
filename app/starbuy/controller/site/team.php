<?php
class starbuy_ctl_site_team extends starbuy_frontpage{


    function __construct($app){
        parent::__construct($app);
        $this->app = $app;
        $this->mdl_special_goods = app::get('starbuy')->model('special_goods');
        $this->mdl_product = app::get('b2c')->model('products');
        $this->mdl_goods = app::get('b2c')->model('goods');
        $this->userObject = kernel::single('b2c_user_object');
        $this->special_pro = kernel::single('starbuy_special_products');
    }

    function index(){
        $get = $this->_request->get_params();
        $pid = intval($get[0]);

        #活动类型
        $stype_id = $get[1];
        $this->pagedata['imageDefault'] = app::get('image')->getConf('image.set');
        $GLOBALS['runtime']['path'] = $this->runtime_path($stype_id,$pid);
        $special_goods = $this->mdl_special_goods->getRow('*',array('product_id'=>$pid));
        $goodsdata = $this->special_pro->getdetailParams($special_goods);
        $this->_set_seo($goodsdata);

        //没有默认货品图片则显示商品所有图片，否则显示关联货品图片
        if(empty($goodsdata['images'])){
            $goodsImages = $this->_get_goods_image($goodsdata['goods']['goods_id']);
            $productBasic['images'] = $goodsImages;//商品图片
            $productBasic['image_default_id'] = $goodsdata['goods']['image_default_id'];//商品图片
        }else{
            $productBasic['images'] = $goodsdata['images'];
            $productBasic['image_default_id'] = $goodsdata['images'][0]['image_id'];//商品图片
        }
        $this->pagedata['page_product_basic'] = $productBasic;

        $goodsdata['discount_rate'] = number_format(($goodsdata['price']/$goodsdata['mktprice'])*10, 1, '.', '');

        $this->pagedata['goodsdata'] = $goodsdata;
        $this->pagedata['stypeid'] = $stype_id;
        $this->pagedata['member_id'] = $this->userObject->get_member_id() ? $this->userObject->get_member_id() : 0;
        $this->pagedata['nowtime'] = time();

        //社会化分享
        $goodsshare = kernel::single('b2c_goods_share')->get_share($productBasic);
        $this->pagedata['goods_share'] = $goodsshare;
        //是否开启商品推荐
        $setting['recommend'] = app::get('b2c')->getConf('goods.recommend');
        $setting['mktprice'] = app::get('b2c')->getConf('site.show_mark_price');
        $this->pagedata['setting'] = $setting;

        //已发布未开始页面缓存
        if( $this->pagedata['nowtime'] >= $goodsdata['release_time'] && $this->pagedata['nowtime'] < $goodsdata['begin_time'] )
        {
            //活动开始的时候缓存过期
            cachemgr::set_expiration($goodsdata['begin_time']);
        }
        //已开始未结束
        elseif( $this->pagedata['nowtime'] >= $goodsdata['begin_time'] && $this->pagedata['nowtime'] < $goodsdata['end_time'] )
        {
            //活动结束的时候缓存过期
            cachemgr::set_expiration($goodsdata['end_time']);
        }

        $this->page('site/product/index.html');
    }

    function _set_seo($goodsdata)
    {
        $seo_goods = array(
            'goods_name' => $goodsdata['goods']['name'],
            'goods_bn' => $goodsdata['goods']['bn'],
            'goods_cat' => $goodsdata['goods']['cat_name'],
            'goods_brand' => $goodsdata['goods']['brand_name'],
            'goods_price' => $goodsdata['promotion_price'],
        );
        $this->setSeo('site_team','index',$seo_goods);
    }


    function _getProduct($filter){
        $products="";
        if($filter){
            $products = $this->mdl_product->getRow("name",array('product_id'=>$filter));
        }
        return $products;
    }


    /*
     *获取商品图片数据
     */
    function _get_goods_image($goods_id){
        $image = app::get("image")->model("image_attach");
        $image_data = $image->getList("attach_id,image_id",array("target_id"=>intval($goods_id),'target_type'=>'goods'));
        foreach($image_data as $img_k=>$img_v){
            $goodsImages[$img_v['attach_id']] = $img_v;
        }
        return $goodsImages;
    }

    /*
     *面包屑
     *
     */
    function runtime_path($type_id,$product_id=null){
        $title = kernel::single('starbuy_special_products')->getTypename(array('type_id'=>$type_id));

        $url = "#";
        if($product_id){
            $url = $this->gen_url(array('app'=>'starbuy', 'ctl'=>'site_special','act'=>'index','arg0'=>$type_id));
        }
        $path = array(
            array(
                'type'=>"goodscat",
                'title'=>"首页",
                'link'=>kernel::base_url(1),

            ),
            array(
                'type'=>"goodscat",
                'title'=>$title,
                'link'=>$url,
            ),
        );

        if($product_id){
            $product = $this->_getProduct($product_id);
            $path[] = array(
                'type'=>"goodscat",
                'title'=>$product['name'],
                'link'=>"#",
            );
        }
        return $path;
    }

    /*
     *时间到时ajax
     *
     */

    function getNowTime(){
        $nowtime = time();
        if($nowtime < $_POST['begin_time']){
            $timenow['status'] = '1';
        }elseif($nowtime >= $_POST['begin_time'] && $nowtime < $_POST['end_time']){
            $timenow['status'] = '2';
        }elseif($nowtime >= $_POST['end_time']){
            $timenow['status'] = '3';
        }
        $timenow['timeNow'] = $nowtime;
        echo json_encode($timenow);
    }

    /*暂时不用
    function check_special($post){
        $url = $this->gen_url(array('app'=>'starbuy', 'ctl'=>'site_team','act'=>'index','arg0'=>$post['product_id'],'arg1'=>$post['type_id']));
        $this->redirect($url);
    }
     */

}
