<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class groupactivity_ctl_site_cart extends b2c_frontpage
{

    function index() {
        #$this->_response->set_header('Cache-Control','no-store');
        $params = $this->_request->get_params(true);
        $id = $params[0];
        if( !$id ) {
            $this->begin(  );
            $this->end(false,'活动不存在！');
        }
        $o = $this->app->model('purchase');
        $arr = $o->getList('*',array('act_id'=>$id));
		$arr = $arr[0];
        if( !$arr ) {
            $this->begin(  );
            $this->end(false,'该活动不存在!');
        }
        if( !$o->validate($arr) ) {
            $this->begin(  );
            $this->end(false,'该活动没有开启!');
        }

        //缓存过期时间 start
        $cache_time = (time()>$arr['start_time'])  //活动已经开始
                        ? ( time()<$arr['end_time'] //未到活动结束时间
                            ? $arr['end_time'] : false )
                        : $arr['start_time'];

        if( $cache_time )
            cachemgr::set_expiration($cache_time);
        //end

        $o->update_state( $arr );
        $arr['buy']=$arr['buy']+$arr['start_value'];
        $this->pagedata['purchase'] = $arr;

        $this->init_goods_html( $arr['gid'] );
        $this->pagedata['goods']['goods_id'] = $arr['gid'];
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['goods']['pic_width'] = $imageDefault['M']['width'];
        $this->pagedata['goods']['pic_height'] = $imageDefault['M']['height'];
        $this->pagedata['goods']['margin_right'] = $imageDefault['M']['width']+8;
        if( (int)$this->pagedata['goods']['price'] )
            $this->pagedata['sales'] = round($arr['price']/$this->pagedata['goods']['price'],2)*10;
        else
            $this->pagedata['sales'] = 0;

        $this->pagedata['check_url'] = $this->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'get_group_info','arg0'=>$arr['act_id']) );
        $this->pagedata['jump_to_cart_url'] = $this->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'checkout') );


        //系统当前时间
        $this->pagedata['request_time_now'] = $this->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'request_time_now') );

        $aPath = array(array('link'=>'true','title'=>'团购：'.$arr['name']));
        $GLOBALS['runtime']['path'] = $aPath;
        $this->pagedata['body_page_list'] = $this->_get_servicelist_by('b2c_products_index_body');

        /**** begin 商品评论 ****/
        $objGoods = app::get('b2c')->model('goods');
        $objComment = kernel::single('b2c_message_disask');
        $aComment = $objComment->good_all_disask($arr['gid']);
        /**** begin 相关商品 ****/
        $oImage = app::get('image')->model('image');
        $imageDefault = app::get('image')->getConf('image.set');
        $aLinkId['goods_id'] = array();
        foreach($objGoods->getLinkList($arr['gid']) as $rows){
            if($rows['goods_1']==$arr['gid']) $aLinkId['goods_id'][] = $rows['goods_2'];
            else $aLinkId['goods_id'][] = $rows['goods_1'];
        }
        if(count($aLinkId['goods_id'])>0){
            $aLinkId['marketable'] = 'true';
            $this->pagedata['goods']['link'] = $objGoods->getList('*',$aLinkId,0,500);
            $this->pagedata['goods']['link_count'] = count($aLinkId['goods_id']);
        }


        $oGoodsLv = app::get('b2c')->model('goods_lv_price');

        $oMlv = app::get('b2c')->model('member_lv');
        $mlv = $oMlv->db_dump( $this->site_member_lv_id,'dis_count' );
        $objProduct = app::get('b2c')->model('products');
        $siteMember = kernel::single( 'b2c_frontpage' )->get_current_member();
        $site_member_lv_id = $siteMember['member_lv'];
        if(is_array($this->pagedata['goods']['link'])){
            foreach ($this->pagedata['goods']['link'] as $key=>&$val) {
                if($val['udfimg']=='true') {
                    if(!$oImage->getList("image_id",array('image_id'=>$val['thumbnail_pic']))) {
                        $val['thumbnail_pic'] = $imageDefault['S']['default_image'];
                    }
                }else{
                    if(!$oImage->getList("image_id",array('image_id'=>$val['image_default_id']))) {
                        $val['image_default_id'] = $imageDefault['S']['default_image'];
                    }
                }
                $temp = $objProduct->getList('product_id, spec_info, price, freez, store,   marketable, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'));
                if( $site_member_lv_id ){
                    $tmpGoods = array();
                    foreach( $oGoodsLv->getList( 'product_id,price',array('goods_id'=>$val['goods_id'],'level_id'=>$site_member_lv_id ) ) as $k => $v ){
                        $tmpGoods[$v['product_id']] = $v['price'];
                    }
                    foreach( $temp as &$tv ){
                        $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                    }
                    $val['price'] = $tv['price'];
                }
                $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                if(!empty($promotion_price['price'])){
                    $val['price'] = $promotion_price['price'];
                    $val['show_button'] = $promotion_price['show_button'];
                    $val['timebuy_over'] = $promotion_price['timebuy_over'];
                }
                $this->pagedata['goods']['link'][$key]['spec_desc_info'] = $temp;
                $this->pagedata['goods']['link'][$key]['product_id'] = $temp[0]['product_id'];
            }
        }

        /**** end 相关商品 ****/

        // 评分类型
        $comment_goods_type = app::get('b2c')->model('comment_goods_type');
        if(!($comment_goods_type->getList('*'))){
            $sdf['type_id'] = 1;
            $sdf['name'] = app::get('b2c')->_('综合评分');
            $addon['is_total_point'] = 'on';
            $sdf['addon'] = serialize($addon);
            $comment_goods_type->insert($sdf);
        }
        $this->pagedata['comment_goods_type'] = $comment_goods_type->getList('*');
        $this->pagedata['point_status'] = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $this->pagedata['base_setting'] = $objComment->get_basic_setting();
        $gask_type = unserialize(app::get('b2c')->getConf('gask_type'));

        if($gask_type){
            foreach($gask_type as $key_ => $val_){
                $gask_type[$key_]['total'] = $objComment->get_ask_total($arr['gid'],$val_['type_id'],'ask');
            }
            $this->pagedata['gask_type'] = $gask_type;
        }

        $objPoint = app::get('b2c')->model('comment_goods_point');
        $this->pagedata['goods_point'] = $objPoint->get_single_point($arr['gid']);
        $this->pagedata['total_point_nums'] = $objPoint->get_point_nums($arr['gid']);
        $this->pagedata['_all_point'] = $objPoint->get_goods_point($arr['gid']);
        $this->pagedata['comment'] = $aComment;

        /**** end 商品评论 ****/

        $setting['buytarget'] = app::get('b2c')->getConf('site.buy.target');
        $this->pagedata['setting'] = $setting;

         if(!$siteMember['member_id']){
            $this->pagedata['login'] = 'nologin';
        }

        $this->pagedata['discuss_status'] = kernel::single('b2c_message_disask')->toValidate('discuss',$arr['gid'],$siteMember,$discuss_message);
        $this->pagedata['discuss_message'] = $discuss_message;
        $this->pagedata['ask_status'] = kernel::single('b2c_message_disask')->toValidate('ask',$arr['gid'],$siteMember,$ask_message);
        $this->pagedata['ask_message'] = $ask_message;

        $sellLogList = $objProduct->getGoodsSellLogList($arr['gid'],0,app::get('b2c')->getConf('selllog.display.listnum'));
        $sellLogSetting['display'] = array(
            'switch'=>$this->app->getConf('selllog.display.switch') ,
            'limit'=>$this->app->getConf('selllog.display.limit') ,
            'listnum'=>$this->app->getConf('selllog.display.listnum')
        );
        $this->pagedata['goods']['setting']['score'] = app::get('b2c')->getConf('site.get_policy.method');
        $this->pagedata['money_format'] = json_encode($ret);
        $this->pagedata['sellLog'] = $sellLogSetting;
        $this->pagedata['sellLogList'] = $sellLogList;
        $this->pagedata['goodsbndisplay'] = app::get('b2c')->getConf('goodsbn.display.switch');
        $this->pagedata['askshow'] = app::get('b2c')->getConf('comment.verifyCode.ask');
        $this->pagedata['goodsBnShow'] = app::get('b2c')->getConf('goodsbn.display.switch');
        $this->pagedata['discussshow'] = app::get('b2c')->getConf('comment.verifyCode.discuss');
        $this->pagedata['showStorage'] = app::get('b2c')->getConf('site.show_storage');
        $this->pagedata['specimagewidth'] = app::get('b2c')->getConf('spec.image.width');
        $this->pagedata['specimageheight'] = app::get('b2c')->getConf('spec.image.height');
        $this->pagedata['goodsRecommend'] = app::get('b2c')->getConf('goods.recommend');
        $this->pagedata['goodsproplink'] = 1;
        $this->pagedata['request_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_product','act'=>'get_goods_spec') );
        $this->set_tmpl('groupactivity');
        $this->page('site/index.html');
    }


    /*
     * return json
     */
    public function get_group_info()
    {
        $params = $this->_request->get_params();
        $id = $params[0];
        if( !$id ) {
            $this->_request->set_http_response_code(404);return;
        }
        $o = $this->app->model('purchase');
        $arr = $o->getList('*',array('act_id'=>$id));
		$arr = $arr[0];
        if( $o->validate($arr) ) {
            if( $arr['state']=='2' ) {
                $return = array('count'=>$arr['buy']+$arr['start_value']);
                echo json_encode( $return );exit;
            } else {
                $this->_request->set_http_response_code(404);return;
            }
        } else {
            $this->_request->set_http_response_code(404);return;
        }
    }
    #End Func


    #End Func
    /*
     * goods html
     */
    private function init_goods_html( $gid )
    {
        $oGoods = app::get('b2c')->model('goods');
        $_GET['act_id'] = $this->pagedata['purchase']['act_id'];
        $pic_arr = $oGoods->getList('image_default_id',array('goods_id'=>$gid));
        if(empty($pic_arr[0]['image_default_id'])){
            $imageDefault = app::get('image')->getConf('image.set');
            $image_id = $imageDefault['M']['default_image'];
        }else{
            $image_id = $pic_arr[0]['image_default_id'];
        }
        $pic = base_storager::image_path($image_id,'m');
        $grouppic = "<img src='".$pic."'>";
        $this->pagedata['goodshtml']['name'] = kernel::single("b2c_goods_detail_name")->show( $gid,$arr_goods );
        $this->pagedata['goodshtml']['pic'] = $grouppic;
        $this->pagedata['goodshtml']['spec'] = kernel::single("b2c_goods_detail_spec")->show( $gid,$arr_goods );
        $this->pagedata['goodshtml']['description'] = kernel::single("b2c_goods_detail_description")->show( $gid,$arr_goods );
        if( !isset($arr_goods['store']) ){
            $arr_goods['store'] = 9999;
        }
        //修改库存
        kernel::single("b2c_goods_detail_store")->init_store( $gid,$arr_goods ); //配置商品真是库存
        $store = $arr_goods['store'];

        // 团购可购买量
        $arr_goods['store'] =  $this->pagedata['purchase']['max_buy']?($this->pagedata['purchase']['max_buy']-$this->pagedata['purchase']['buy']+$this->pagedata['purchase']['start_value']):($arr_goods['store']?$arr_goods['store']:99999);
        $arr_goods['store'] = $arr_goods['store']>$store ? $store : $arr_goods['store'];

        $purchase = $this->pagedata['purchase'];
        if( $purchase['alluser']=='true' ) { //所有会员情况 每单限购
            $arr_goods['_real_store'] = ($arr_goods['store']>$puchase['orderlimit']) ? $purchase['orderlimit'] : $arr_goods['store'];
        } else {
            //$sum 该会员已经购买的数量
            kernel::single('groupactivity_cart_object_purchase')->get_purchase_sum( $purchase,$sum);
            $p = $purchase['userlimit']-$sum;
            $arr_goods['_real_store'] = ($arr_goods['store']>$p) ? $p : $arr_goods['store'];
            $arr_goods['_real_store'] = $arr_goods['_real_store']>0 ? $arr_goods['_real_store'] : 0;//如果库存小于0，则真实库存显示0
        }

        //扯谈 。。。。。。。。。。。。。。。
        if( $arr_goods['store']<=0 && $arr_goods['nostore_sell']!=1)  {
            $this->pagedata['purchase']['state'] = 3;
            //修改数据库
            $tmp = $this->pagedata['purchase'];
            $save = array('act_id'=>$tmp['act_id']);
            if( $tmp['buy']>$tmp['min_buy'] ) {
                $save['state'] = 3;
            } else {
                if( $save['buy'] )
                    $save['state'] = 4;
                else
                    $save['state'] = 5;
            }
            $this->app->model('purchase')->save( $save );
            unset($save);
            #print_r($this->pagedata['purchase']);exit;
        }


        $this->pagedata['goodshtml']['store'] = kernel::single("b2c_goods_detail_store")->show( $gid,$arr_goods );
        if(!$arr_goods['price'] || $arr_goods['price']==0){
            $arr_goods['price'] = $arr_goods['current_price'];
        }
        $product_tmp = array_values($arr_goods['product']);
        if(count($product_tmp)==1 && $product_tmp[0]['spec_info']=='' && $product_tmp[0]['spec_desc']==''){
            $this->pagedata['is_spec'] = 'false';
        }else{
            $this->pagedata['is_spec'] = 'true';
        }
        $this->pagedata['goods'] = $arr_goods;
        if( $arr_goods['status']!='true' ) {
            $this->begin(  );
            $this->end( false,'该商品已经下架！不能参加团购！' );
        }
        #print_r($arr_goods);exit;
    }
    #End Func

    /*
     * 团购商品checkout
     */
    public function checkout()
    {
        $o = kernel::single("groupactivity_cart_object_purchase");
        $type = $o->get_type();
        if(!$data = $this->getData()){
            //登录跳转过来
            $data = !empty($_SESSION['groupactivity-redirect']) ? $_SESSION['groupactivity-redirect'] : array();
        }
        if(!empty($_SESSION['groupactivity-redirect'])) unset($_SESSION['groupactivity-redirect']);
        $o->set_session( array($type=>$data) );

        if( !$o->_check() ) {
            $this->begin( $this->gen_url( array('app'=>'groupactivity','act'=>'index','ctl'=>'site_cart','arg0'=>$o->get_group_id()) ) );
            $this->end( false,$o->error_html );
        }

        $url = $this->gen_url( array('app'=>'b2c','act'=>'checkout','ctl'=>'site_cart','arg0'=>'group') );
        $this->redirect( array('app'=>'b2c','act'=>'checkout','ctl'=>'site_cart','arg0'=>'group') );
    }
    #End Func


     function getData() {
        $data = $this->_request->get_params(true);
        $data = $data['goods'];
        $arr['group'] = 1;

        if( !$data['goods_id'] ) return false;
        if( !$data['product_id'] ) {
            $tmp = app::get('b2c')->model('products')->getList( '*',array('goods_id'=>$data['goods_id']) );
            if( count($tmp) >1 ) return false;
            if( !$tmp ) return false;
            reset( $tmp );
            $tmp = current( $tmp );
            $data['product_id'] = $tmp['product_id'];
        }
        $arr['goods'] = array(
            'goods_id' => $data['goods_id'],
            'product_id' => $data['product_id'],
        );
        $arr['goods']['num'] = $data['num'];
        return $arr;
    }


    //返回系统当前时间
    public function request_time_now() {
        echo time();exit;
    }

    public function request_widget_data() {
        $arr = $this->_request->get_post();
        if(!is_int(intval($arr['act_id'])) || !is_int(intval($arr['goods_id']))){
	        return;
    	}
        $sql="select buy,max_buy from sdb_groupactivity_purchase where act_id=".$arr['act_id']." and gid=".$arr['goods_id'];
        $goods=kernel::database()->selectrow($sql);
        $data['inventory']=$goods['max_buy']-$goods['buy'];
        $data['timeNow']=time();
        echo json_encode($data);
    }

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
}
