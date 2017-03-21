<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * ctl_cart
 *
 * @uses b2c_frontpage
 * @package
 * @version $Id: ctl.cart.php 1952 2008-04-25 10:16:07Z flaboy $
 * @copyright 2003-2007 ShopEx
 * @author <kxgsy163@163.com>
 * @license Commercial
 */

class gift_ctl_site_gift extends gift_frontpage{


    function __construct($app){
        parent::__construct($app);
        $shopname = app::get('site')->getConf('site.name');
        $this->shopname = $shopname;
        if(isset($shopname)){
            $this->title = app::get('b2c')->_('商品页_').$shopname;
            $this->keywords = app::get('b2c')->_('商品页_').$shopname;
            $this->description = app::get('b2c')->_('商品页_').$shopname;
        }
        $this->app_b2c = app::get('b2c');
    }

    /*
     * 赠品列表页基本设置获取
     * */
    private function _get_lists_setting(){
        $app = $this->app_b2c;
        $setting['open'] = $app->getconf('site.get_policy.method');//是否使用积分 1不使用积分
        $setting['site_point_usage'] = $app->getConf('site.point_usage');//积分用途 1 用于兑换
        $imageDefault = app::get('image')->getConf('image.set');
        $setting['imageDefault'] = $imageDefault;
        $setting['time'] = time();
        return $setting;
    }

    /*
     * 赠品详情页配置
     * */
    private function _get_setting(){
        $app = $this->app_b2c;
        $setting = $this->_get_lists_setting();
        $setting['buytarget'] = $app->getConf('site.buy.target');//购买跳转方式
        $setting['goodsbn'] = $app->getConf('goodsbn.display.switch');//是否启用商品编号
        $setting['goodsprop'] = $app->getConf('goodsprop.display.position');//属性显示位置
        //判断是否是IE浏览器并且检查版本 （ajax选择货品的时候改变浏览器地址栏地址 IE不支持）
        if($_SERVER['HTTP_USER_AGENT'] && preg_match('/MSIE\s+([0-9.]+)/',$_SERVER['HTTP_USER_AGENT'],$matches)){
            $setting['Browser']['IE'] = true;
            $setting['Browser']['version'] = (int)$matches[1];
        }
        return $setting;
    }

    public function index() {
        //获取参数 货品ID
        $_getParams = $this->_request->get_params();
        $productId = $_getParams[0];

        $userObject = kernel::single('b2c_user_object');
        if( !$userObject->is_login()  ){
            $this->pagedata['login'] = 'nologin';
        }

        $this->get_gift_basic($productId);

        //面包屑
        $aPath = array(
            array('link'=>$this->gen_url( array('app'=>'gift','act'=>'lists','ctl'=>'site_gift') ),'title'=>'赠品列表页'),
            array('link'=>'true','title'=>$this->pagedata['gift']['name']),
        );
        $GLOBALS['runtime']['path'] = $aPath;

        //seo
        $this->setSeo('site_gift','index',$this->prepareSeoData($this->pagedata));
        $this->set_tmpl('gift');
        $this->page('site/product/index.html');
    }

    public function get_gift_basic($productId){
        $this->pagedata['setting'] = $this->_get_setting();
        $giftData = $this->app->model('ref')->getList('*',array('product_id'=>$productId));
        // 赠品是否有效
        if( !$giftData || $giftData === false) {
            kernel::single('site_router')->http_status(404);return;
        }

        $giftSdf = $giftData[0];
        $goodsData = $this->app_b2c->model('goods')->getList('*',array('goods_id'=>$giftSdf['goods_id'],'goods_type'=>array('gift','normal')));
        $giftSdf['goods'] = $goodsData[0];
        $produtData= $this->app_b2c->model('products')->getList('*',array('product_id'=>$productId));
        $giftSdf['product'] = $produtData[0];
        if($giftSdf['cat_id']){
            $catData = $this->app->model('cat')->getList($giftSdf['cat_id'] );
            $giftSdf['cat'] = $catData;
        }

        $aGoods = $goodsData[0];
        $aGoods['product'] = $produtData[0];
        //规格
        $goodsSpec = $this->_get_goods_spec($giftSdf['goods_id'],$aGoods);
        $giftSdf['spec'] = $goodsSpec;

        //货品图片
        $image = app::get("image")->model("image_attach");
        $image_data = $image->getList("attach_id,image_id",array("target_id"=>intval($giftSdf['goods_id']),'target_type'=>'goods'));
        foreach($image_data as $img_k=>$img_v){
            $goodsImages[$img_v['attach_id']] = $img_v;
        }
        $giftSdf['images'] = $goodsImages;//商品图片

        //赠品兑换权限
        $giftSdf = $this->_check_permission($giftSdf);
        $this->pagedata['gift'] = $giftSdf;

        if( isset( $giftSdf['member_lv_ids'] ) ) {
            $tmp_member_lv_info = $this->app->model('member_lv')->getList('member_lv_id,name',
                array(
                    'member_lv_id'=>( is_array( $giftSdf['member_lv_ids'] ) ? $giftSdf['member_lv_ids'] : explode(',', $giftSdf['member_lv_ids']) ),
                ),
                0, -1, 'member_lv_id ASC');
            foreach($tmp_member_lv_info as $row){
                $member_lv[] = $row['name'];
            }
            $this->pagedata['member_lv'] = implode('，',$member_lv);
        }
    }

    function prepareSeoData($data){
        return array(
            'shop_name'=>$this->shopname,
            'goods_name'=>$data['goods']['name'],
        );
    }

    /*
     * 检查赠品兑换权限
     * */
    private function _check_permission($giftSdf){
        //检查是否开启积分
        $open = $this->app_b2c->getconf('site.get_policy.method');//是否使用积分 1不使用积分
        if($open == '1'){
            $giftSdf['permission'] = 'false';
            $giftSdf['permissionMsg'] = $this->app_b2c->_('系统未开启积分，不可兑换');
            return $giftSdf;
        }

        //检查积分是否可以用于兑换
        $site_point_usage = $this->app_b2c->getConf('site.point_usage');//积分用途 1用于兑换
        if($site_point_usage != '1'){
            $giftSdf['permission'] = 'false';
            $giftSdf['permissionMsg'] = $this->app_b2c->_('积分只用于抵扣，不可兑换');
            return $giftSdf;
        }

        //检查赠品是否开始兑换
        if($giftSdf['from_time'] > time()){
            $giftSdf['permission'] = 'false';
            $giftSdf['permissionMsg'] = $this->app_b2c->_('未到兑换时间，暂时不可兑换');
            return $giftSdf;
        }

        //检查赠品是否已过兑换时间
        if($giftSdf['to_time'] < time()){
            $giftSdf['permission'] = 'false';
            $giftSdf['permissionMsg'] = $this->app_b2c->_('已过兑换时间，不可兑换');
            return $giftSdf;
        }

        //检查所在赠品分类是否发布 | 商品是否下架 | 货品是否下架
        if( ($giftSdf['cat'] && $giftSdf['cat']['ifpub'] == 'false') || $giftSdf['goods']['marketable'] == 'false' || $giftSdf['product']['marketable'] == 'false'){
            $giftSdf['marketable'] = 'false';
        }

        //赠品下架
        if($giftSdf['marketable'] == 'false'){
            $giftSdf['permission'] = 'false';
            $giftSdf['permissionMsg'] = $this->app_b2c->_('赠品已下架，不可兑换');
            return $giftSdf;
        }


        $giftSdf['permission'] = 'true';
        return $giftSdf;

    }

    //获取货品规格数据
    private function _get_goods_spec($gid,$aGoods){
        $refData = $this->app->model('ref')->getList('*',array('goods_id'=>$gid));
        if(count($refData) <= 1 ) return false;

        foreach($refData as $row){
            $pids[] = $row['product_id'];
        }
        $goodsSpec = array();
        $products = app::get('b2c')->model('products')->getList('product_id,spec_desc,store,freez,marketable',array('product_id'=>$pids));
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
            $arrSpecName = app::get('b2c')->model('specification')->getList('spec_name,spec_id,spec_type',$arrSpecId);
            foreach($arrSpecName as $specItem){
                $goodsSpec['specification']['spec_name'][$specItem['spec_id']] = $specItem['spec_name'];
                $goodsSpec['specification']['spec_type'][$specItem['spec_id']] = $specItem['spec_type'];

            }
        }
        return $goodsSpec;
    }

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
                if(empty($goodsProps[$i]['name']) || empty($goodsProps[$i]['value']) ){
                    unset($goodsProps[$i]);
                    continue;
                }
            }
        }
        ksort($goodsProps);
        return $goodsProps;
    }

    /*
     * 获取赠品标准数据
     * */
    private function _get_gift_sdf($gift_list){
        $app_b2c = app::get('b2c');
        if(empty($gift_list) || !is_array($gift_list) ) return false;
        foreach($gift_list as $k=>$row){
            $key = $row['goods_id'];
            $list[$key] = $row;
            $list[$key]['store']= $row['max_limit']-$row['real_limit'];//最大能兑换的数量
            $gids[] = $row['goods_id'];
        }

        $goodsData = $app_b2c->model('goods')->getList('goods_id,brief,name,marketable,nostore_sell,image_default_id,store,price',array('goods_id'=>$gids,'goods_type'=>array('gift','normal')));
        foreach($goodsData as $goods_row){
            $key = $goods_row['goods_id'];
            if($goods_row['nostore_sell'] == 'true'){
                $goods_row['store'] = 999999;
            }
            $list[$key]['goods'] = $goods_row;
        }
        return $list;
    }

    /*
     * 赠品列表页获取基本数据条件设置
     * */
    private function _get_lists_gift_filter(){
        //获取条件
        $filter['marketable'] = 'true'; //已发布的赠品
        $filter['goods_type'] = array('gift','normal'); //指定类型
        #$filter['to_time|than'] = time();
        $this->app->model('goods')->unuse_filter_default( false );
        return $filter;
    }

    /*
     * 赠品列表页
     * */
    public function lists($page=1) {
        //面包屑
        $aPath = array(array('link'=>'true','title'=>'赠品列表页'));
        $GLOBALS['runtime']['path'] = $aPath;

        //基本配置
        $setting = $this->_get_lists_setting();
        $this->pagedata['setting'] = $setting;

        //条件
        $filter = $this->_get_lists_gift_filter();
        $pageLimit = 20;//默认为20条

        //当前页数据
        $giftRefModel = $this->app->model('ref');
        $gift_list = $giftRefModel->get_list_finder('*', $filter, $pageLimit*($page-1),$pageLimit,'`order` asc');
        $this->pagedata['data'] = $this->_get_gift_sdf($gift_list);
        //总数
        $count =  $giftRefModel->count_finder($filter);

        //分页
        $token = md5("page{$page}");
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit),
            'link'=>$this->gen_url(array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'lists','args'=>array($token=time()))),
            'token'=>$token
        );

        $this->setSeo('site_gift','lists',array('shop_name'=>$this->shopname));
        $this->set_tmpl('gift');
        $this->page('site/gallery/index.html');
    }

    /*
     * 相册
     * */
    public function albums( $goodsid, $selected='def' ){
        $objGoods = $this->app->model('goods');
        $o = app::get('image')->model('image_attach');
        $dImg = $o->getList('*',array('target_id'=>$goodsid));
        $aGoods = $objGoods->dump_b2c( array('goods_id'=>$goodsid),'name' );

        $this->pagedata['goods_name'] = urlencode(htmlspecialchars($aGoods['name'],ENT_QUOTES));
        $this->pagedata['goods_name_show'] = $aGoods['name'];
        $this->pagedata['company_name'] = str_replace("'","&apos;",htmlspecialchars(app::get('site')->getConf('site.name')));
        if(!$dImg){
            $imageDefault = app::get('image')->getConf('image.set');
            $dImg[]['image_id'] = $imageDefault['L']['image_id'];
        }
        $this->pagedata['image_file'] = $dImg;
        if($selected=='def'){
            $selected=current($dImg);
            $selected=$selected['target_id'];
        }
        $this->pagedata['selected'] = $selected;
        $this->page('site/product/albums.html',true,'b2c');

    }


    public function ajax_gift_basic($pid){
        $this->get_gift_basic($pid);
        echo $this->fetch('site/product/info/basic.html');exit;
    }

    public function ajax_gift_store($pid){
        $giftData = $this->app->model('ref')->getList('*',array('product_id'=>$pid));
        // 赠品是否有效
        if( !$giftData || $giftData === false) {
            kernel::single('site_router')->http_status(404);return;
        }

        $giftSdf = $giftData[0];
        $goodsData = $this->app_b2c->model('goods')->getList('*',array('goods_id'=>$giftSdf['goods_id'],'goods_type'=>array('gift','normal')));
        $giftSdf['goods'] = $goodsData[0];
        $produtData= $this->app_b2c->model('products')->getList('*',array('product_id'=>$pid));
        $giftSdf['product'] = $produtData[0];
        /*--库存--*/
        if(is_null($giftSdf['product']['store'])){
            $productRealStore = 999999;
        }else{
            $productRealStore = $giftSdf['product']['store'] - $giftSdf['product']['freez'];
        }
        $giftRealStore = $giftSdf['max_limit'] - $giftSdf['real_limit'];
        if(($giftRealStore !== '' && $giftRealStore <=0) || $productRealStore <= 0){
            $store = 0;
            #$giftSdf['permission'] = 'false';
            #$giftSdf['permissionMsg'] = $this->app_b2c->_('已兑换完，不可兑换');
        }else{
            if($giftRealStore !== '' && ($giftRealStore < $productRealStore) ){
                $store = $giftRealStore;
            }else{
                $store = $productRealStore;
            }
        }

		$store = intval($giftSdf['max_buy_store']) > $store ?  $store : intval($giftSdf['max_buy_store']);
        /*--end--*/
        echo json_encode(array('store'=>$store));exit;
    }
    public function add_to_cart() {
        $arr = $this->get_data();
        $gift_id = $arr['gift_id'];

        if(($return=kernel::single('gift_cart_object_gift')->add( array('gift'=>$arr) ))!==true) {
            if( !is_array($return) ) {
                if( $_POST['mini_cart'] ) {
                    echo json_encode( array('error'=>'赠品不存在') );exit;
                } else {
                    $this->begin($this->gen_url(array('app'=>'gift', 'ctl'=>'site_gift', 'act'=>'lists')));
                    $this->end(false, app::get('gift')->_('赠品不存在！'), '', '', true);return;
                }
            }
        } else {
            if( $_POST['mini_cart'] ) {
                $this->app = app::get('b2c');
                $arr = $this->app->model("cart")->get_objects();
                $temp = $arr['_cookie'];

                $this->pagedata['cartCount']      = $temp['CART_COUNT'];
                $this->pagedata['cartNumber']     = $temp['CART_NUMBER'];
                $this->pagedata['cartTotalPrice'] = $temp['CART_TOTAL_PRICE'];

                $this->page('site/cart/mini_cart.html', true);
                return;
            } else {
                unset($return);
                $return['begin'] = array('app'=>'b2c', 'ctl'=>'site_cart', 'act'=>'index');
                $return['end']   = array('status'=>true,  'msg'=>app::get('gift')->_('加入购物车成功！'));
            }
        }

        if( $_POST['mini_cart'] && ( $return['end']['status']==false ) ){
            echo json_encode( array('error'=>$return['end']['msg']) );exit;
        } else {
            $this->begin($this->gen_url($return['begin']));
            $this->end($return['end']['status'], $return['end']['msg'], '', '', true);
        }
    }

    public function remove_cart_to_disabled() {
        kernel::single('base_session')->start();
        $_obj_type  = $this->_request->get_param(0);
        $_obj_ident  = $this->_request->get_param(1);
        $_product_id = (int)$this->_request->get_param(2);
        $_SESSION['cart_objects_disabled_item'][$_obj_type][$_obj_ident][$_product_id] = 'true';
        kernel::single('site_router')->http_status(404);return;
    }
}

