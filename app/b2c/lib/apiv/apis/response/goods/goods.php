<?php
class b2c_apiv_apis_response_goods_goods
{
    public function __construct($app)
    {
        $this->app=$app;    
    }
    /**
     * 根据商品id获取商品详情
     * @param int goods_id 商品id
     * @return goods_id 商品的id 
     * @return goods_context 商品的详情 
     */
    public function get_goods_intro($params, &$service)
    {
        $goods_id = $params['goods_id'];
        if(empty($goods_id)){
            return $service->send_user_error('商品ID必填');  
        } 
        $obj_goods = $this->app->model('goods');
        $filter = array('goods_id'=>intval($goods_id));
        $wap_status = app::get('wap')->getConf('wap.status');
        if( $wap_status == 'true' ){
            $intro = $obj_goods->getList('wapintro,intro',$filter);
        }else{
            $intro = $obj_goods->getList('intro',$filter);
        }
        $intro = $intro[0];
        $return['goods_id'] = $goods_id;
        if( $intro['wapintro'] ){
            $return['goods_context'] = $intro['wapintro'];
        } else {
            $return['goods_context'] = $intro['intro'];
        }
        return $return;
    }

    /**
     * 根据筛选条件查询商品
     * @param int page_num 页码
     * @param int page_size 每页的容量
     * @param int cat_id 商品分类 
     * @param string search_keywords 关键词（根据名字搜索）
     * @param string brand_id array(int)数组的json(品牌id)
     * @param string specs array(int=>array(int))格式的json 商品规格array(规格id=>array(规格值id))
     * @param string props array(int=>array(int))格式的json props 商品属性id
     * @return goods 商品
     * @return count 商品条目数
     */
    public function search_properties_goods($params, &$service)
    {
        //json转array
        $params['brand_id'] = $params['brand_id'] ? json_decode($params['brand_id'],true) : null;
        $params['specs'] = $params['specs'] ? json_decode($params['specs'],true) : null;
        $params['props'] = $params['props'] ? json_decode($params['props'],true) : null;
        //分类、品牌、关键词必须要有一个才可以查询
        if( $params['cat_id'] == null && $params['search_keywords'] == null && $params['brand_id'] == null)
        {
            return array('status'=>'error','message'=>'分类、品牌、关键词至少有一项需要填写');
        }

        $obj_goods = $this->app->model('goods');
        $limit = $params['page_size'] ? $params['page_size'] : 10;
        $offset = $params['page_num'] ? (($params['page_num']-1) * $limit) : 0;

        //根据分类查询
        if(isset($params['cat_id']) && $params['cat_id']!=null)
        {
            $obj_cat = $this->app->model('goods_cat');
            $cat_data = $obj_cat->getList('cat_id',array('parent_id|in'=>$params['cat_id']));
            foreach($cat_data as $value)
            {
                $cat_filter[$value['cat_id']] = $value['cat_id'];
            }
            $cat_filter[$params['cat_id']] = $params['cat_id'];
            $filter['cat_id|in'] = $cat_filter;
        }

        //根据关键字查询
        if(isset($params['search_keywords']) && $params['search_keywords']!=null)
        {
            $filter['search_keywords'] = array($params['search_keywords']);
        }

        //根据品牌查询
        if(isset($params['brand_id']) && count($params['brand_id'])>0)
        {
            $filter['brand_id'] = $params['brand_id'];
        }

        //根据属性查询
        if(isset($params['props']) && count($params['props'])>0)
        {
            foreach($params['props'] as $prop_id=>$prop)
            {
                $prop_ids[$prop_id] = $prop_id;
            }

            $obj_props = $this->app->model('goods_type_props');
            $props = $obj_props->getList('props_id,goods_p',array('props_id|in'=>$prop_ids));
            foreach($props as $prop)
            {
                $filter['p_'.$prop['goods_p'].'|in'] = $params['props'][$prop['props_id']];
            }
        }

        //根据规格查询
        if(isset($params['specs']) && count($params['specs'])>0)
        {
            foreach($params['specs'] as $spec_id=>$spec)
            {
                $filter['s_'.$spec_id] = $spec;
            }
        }

        //排序
        if(isset($params['orderBy_id']) && $params['orderBy_id']>0 && $params['orderBy_id'] <11)
        {
            $order = $obj_goods->orderBy($params['orderBy_id']);
            $orderBy = $order['sql'];
        }
        $filter['marketable'] = 'true';
        $data = $obj_goods->getList('marketable,goods_id,bn,name,brief,image_default_id,comments_count,nostore_sell',$filter,$offset,$limit,$orderBy);

        foreach($data as $key=>$goods)
        {
            $fmt_use_for_img[$goods['goods_id']] = $goods['image_default_id'];
            $gids[$goods['goods_id']] = $goods['goods_id'];
        }
        $fmt_image = $this->get_images_by_ids($fmt_use_for_img);
        //拉取默认货品
        $obj_product = $this->app->model('products');
        $products = $obj_product->getList('product_id,goods_id,price,mktprice,store,freez',array('goods_id'=>$gids,'is_default'=>'true','marketable'=>'true'));
        foreach($products as $key=>$value)
        {
            $fmt_products[$value['goods_id']] = $value;
        }
        //组织数据
        foreach($data as $key=>$goods)
        {
            $data[$key]['default_product_id'] = $fmt_products[$goods['goods_id']]['product_id'];
            $data[$key]['price'] = $fmt_products[$goods['goods_id']]['price'];
            $mktprice = $fmt_products[$goods['goods_id']]['mktprice'];
            if($mktprice == '' || $mktprice == null)
                $data[$key]['mktprice'] = $obj_product->getRealMkt($fmt_products[$goods['goods_id']]['price']);
            else
                $data[$key]['mktprice'] = $mktprice;
            $store = $fmt_products[$goods['goods_id']]['store'];
            $freez = $fmt_products[$goods['goods_id']]['freez'];
            if($goods['nostore_sell'] || $store == null)
                $data[$key]['store'] = 999999;
            else
                $data[$key]['store'] = $store - $freez;
            $data[$key]['image'] = $fmt_image[$goods['image_default_id']];
            unset($data[$key]['nostore_sell']);
        }
        $return['goods']=$data;
        //获取总条数
        $count = $obj_goods->countGoods($filter);
        $return['count']=$count;
        return $return;
    }

    public function get_goods_detail($params, $services)
    {
        if (isset($params['product_id']) && $params['product_id'] != null)
        {
            $product_id = $params['product_id'];
        } else {
            return array('status'=>null,'message'=>'请输入商品货品id');
        }

        //拉取货品
        $obj_product = $this->app->model('products');
        $product = $obj_product->getRow('*', array('product_id'=>$product_id));
        if($product == null) return array('status'=>'','message'=>'该货品不存在');

        //拉取商品
        $obj_goods = $this->app->model('goods');
        $goods = $obj_goods->dump($product['goods_id']);
        if($goods == null) return array('status'=>'','message'=>'未找到该货品对应的商品');

        //拉取库存
        if($goods['nostore_sell'] == '1' || $product['store'] == null)
        {
            $store = 999999;
        }else{
            $store = $product['store'] - $product['freez'];
        }

        //拉取类型type
        $obj_type = $this->app->model('goods_type');
        $type = $obj_type->getRow('type_id,name',$goods['type']);

        //拉取分类
        $obj_category = $this->app->model('goods_cat');
        $category = $obj_category->getRow('cat_id,cat_name',$goods['category']);

        //拉取品牌
        $obj_brand = $this->app->model('brand');
        $brand = $obj_brand->getRow('brand_id,brand_name',$goods['brand']);

        //拉取促销
        $promotion = @$this->get_promotion_by_goods_id($goods['goods_id']);

        //处理规格(并拉取图片)
        $spec = $goods['spec'];
        foreach($spec as $spec_key=>$spec_value)
        {
            foreach($spec_value['option'] as $spec_option_key=>$spec_option_value)
            {
                $image_ids[$spec_option_value['spec_image']] = $spec_option_value['spec_image'];
                $spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'] = explode(',',$spec_option_value['spec_goods_images']);
                foreach($spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'] as $image_id)
                {
                    $image_ids[$image_id] = $image_id;
                }
            }
        }
        $obj_image_attach = app::get("image")->model("image_attach");
        $image_data_ids = $obj_image_attach->getList("attach_id,image_id",array("target_id"=>intval($goods['goods_id']),'target_type'=>'goods'));
        foreach($image_data_ids as $images)
        {
            $image_ids[$images['image_id']] = $images['image_id'];
        }
        $fmt_images = $this->get_images_by_ids($image_ids);

        foreach($spec as $spec_key=>$spec_value)
        {
            foreach($spec_value['option'] as $spec_option_key=>$spec_option_value)
            {
                $spec[$spec_key]['option'][$spec_option_key]['spec_image'] = $fmt_images[$spec_option_value['spec_image']];
                foreach($spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'] as $image_key=>$image_id)
                {
                    $spec[$spec_key]['option'][$spec_option_key]['spec_goods_images'][$image_key] = $fmt_images[$image_id];
                }
            }
        }
        foreach($image_data_ids as $goods_image)
        {
            $image[$goods_image['image_id']] = $fmt_images[$goods_image['image_id']];
        }

        //获取商品属性
        $props = $goods['props'];
        foreach($props as $p_id=>$value_id)
        {
            $props_value_ids[$p_id] = $value_id['value'];
        }
        $obj_props_value = $this->app->model('goods_type_props_value');
        $props_value = $obj_props_value->getList('props_value_id,props_id,name,alias',array('props_value_id'=>$props_value_ids));
        foreach($props_value as $value)
        {
            $fmt_props_value[$value['props_value_id']] = $value;
            $props_ids[$value['props_id']] = $value['props_id'];
        }
        $obj_props = $this->app->model('goods_type_props');
        $props_sdf = $obj_props->getList('props_id,name,goods_p',array('props_id'=>$props_ids));
        foreach($props_sdf as $pp)
        {
            $fmt_props['p_'.$pp['goods_p']] = $pp;
        }
        foreach($fmt_props as $key=>$value)
        {
            $use_props[$key]['props'] = $fmt_props[$key];
            $use_props[$key]['props_value'] = $fmt_props_value[$props[$key]['value']];
        }
        //组织数据
        $return['goods_id'] = $product['goods_id'];
        $return['product_id'] = $product_id;
        $return['product_bn'] = $product['bn'];
        $return['unit'] = $product['unit'];
        $return['price'] = $product['price'];
        $return['mktprice'] = $product['mktprice'] ? $product['mktprice'] : $obj_product->getRealMkt($product['price']);
        $return['product_marketable'] = $product['marketable'];
        $return['goods_marketable'] = $goods['maketable'];
        $return['title'] = $goods['name'];
        $return['brief'] = $goods['brief'];
        $return['type_name'] = $type['name'];
        $return['store'] = $store;
        $return['cat_name'] = $category['cat_name'];
        $return['brand'] = $brand;
        $return['spec'] = $spec;
        $return['promotion'] = $promotion;
        $return['props'] = $use_props;
        $return['image'] = $image;
        return $return;
    }

    private function get_promotion_by_goods_id($goods_id)
    {
        $goodsPromotion = kernel::single('b2c_goods_object')->get_goods_promotion($goods_id);
        $productPromotion = array();
        $giftId = array();
        //商品促销
        foreach($goodsPromotion['goods'] as $row){
            $temp = is_array($row['action_solution']) ? $row['action_solution'] : @unserialize($row['action_solution']);
            if(key($temp) == 'gift_promotion_solutions_gift'){
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
            sort($image);
            foreach($aGift as $key=>$row){
                if($image[$key]['marketable'] == 'false'){
                    unset($aGift[$key]);continue;
                }
                $aGift[$key]['image_default_id'] = $image[$key]['image_default_id'];
                if($row['nostore_sell']){
                    $aGift[$key]['store'] = 999999;
                }
            }
            $productPromotion['gift'] = $aGift;
        }
        return $productPromotion;
    }

    private function get_images_by_ids($image_ids)
    {
        $obj_image = app::get('image')->model('image');
        $image_from_db = $obj_image->getList('image_id,storage,l_url,m_url,s_url',array('image_id|in'=>$image_ids));
        foreach($image_from_db as $imageRow)
        {
            $image_id = $imageRow['image_id'];
            $fmt_image[$image_id]['image_id'] = $image_id;
            $fmt_image[$image_id]['s_url'] = $imageRow['s_url'] ? $imageRow['s_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['s_url'] &&!strpos($fmt_image[$image_id]['s_url'],'://')){
                $fmt_image[$image_id]['s_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['s_url'];
            }
            $fmt_image[$image_id]['m_url'] = $imageRow['m_url'] ? $imageRow['m_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['m_url'] &&!strpos($fmt_image[$image_id]['m_url'],'://')){
                $fmt_image[$image_id]['m_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['m_url'];
            }
            $fmt_image[$image_id]['l_url'] = $imageRow['l_url'] ? $imageRow['l_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['l_url'] &&!strpos($fmt_image[$image_id]['l_url'],'://')){
                $fmt_image[$image_id]['l_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['l_url'];
            }
        } 
        return $fmt_image;
    }

}
