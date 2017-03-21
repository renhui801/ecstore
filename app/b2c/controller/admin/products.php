<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_products extends desktop_controller{
    /*
     * 编辑货品入口方法
     * */
    function set_spec_index(){
        if(!$this->has_permission('editgoods')){//没有编辑权限则没有编辑货品权限
            header('Content-Type:text/html; charset=utf-8');
            echo app::get('desktop')->_("您无权操作");exit;
        }

        #开启规格／编辑货品 提供规格关联商品图片
        $this->pagedata['goods_spec_images'] = $this->_goods_spec_images();

        //修改规格图片跳转链接
        $this->pagedata['spec_image_request_url'] = "&quot;index.php?app=desktop&act=alertpages&goto=".urlencode("index.php?app=image&ctl=admin_manage&act=image_broswer")."&quot;";

        $goods_id = $_GET['goods_id'];
        $oGoods = $this->app->model('goods');

        if($_GET['nospec'] == 1){
            $type_id = $_GET['type_id'];
            $params_spec = array();
        }
        else{
            $goods = $oGoods->dump($goods_id,'goods_id,type_id,spec_desc');
            $params_spec = $goods['spec'];
            $type_id = $goods['type']['type_id'];
        }

        $goods_info = $this->_get_goods_info($_GET['goods']['product'][0]);
        $this->pagedata['goods_info'] = $goods_info ? json_encode($goods_info) : false;

        //规格数据
        $result  = $this->set_spec($type_id, $params_spec);
        $this->pagedata['spec'] = $result['all_spec'];

        if(!$_GET['nospec'])//如果开启过规格则需要返回货品数据和选中的规格数据
        {
            $this->pagedata['selection_spec'] = $result['selection_spec'];
            $products = $this->getProducts($goods_id);

            $active = $this->_pre_recycle_spec($goods_id,$products);
            $this->pagedata['activeSpec'] = $active['activeSpec'];//不能删除的规格(有活动订单)
        }

        $this->pagedata['selection_spec_json'] = $result['selection_spec'] ? json_encode($result['selection_spec']) : false;
        $this->pagedata['products'] = $products ? json_encode($products) : false;
        sort($active['activeProducts']);
        $this->pagedata['activeProducts'] = json_encode($active['activeProducts']); //不能删除的货品(有活动订单)

        $this->pagedata['goods_id'] = $goods_id;
        $this->pagedata['type_id'] = $_GET['type_id'];

        $this->singlepage('admin/goods/detail/spec/set_spec.html');
    }

    /*
     * 开启规格的时候，格式化GET传过来的商品基本信息
     * */
    private function _get_goods_info($goods_info){
        if($goods_info) {
            $goods = $goods_info;
            $goods['price'] = $goods_info['price']['price']['price'];
            $goods['cost'] = $goods_info['price']['cost']['price'];
            $goods['mktprice'] = $goods_info['price']['mktprice']['price'];
            if($goods_info['price']['member_lv_price']){
                $goods['member_lv_price'] = $goods_info['price']['member_lv_price'];
            }
            return $goods;
        }
    }

    /*
     * 开启规格／编辑货品 提供规格关联商品图片
     * */
    private function _goods_spec_images(){
        if($_GET['goods']['images']){
            $goods_spec_images = $_GET['goods']['images'];
        }
        else{
            $oImage = app::get('image')->model('image_attach');
            $image_arr = $oImage->getList('image_id',array('target_id'=>$_GET['goods_id'],'target_type'=>'goods'));
            $image_arr_tmp = array();

            foreach($image_arr as $k=>$v)
            {
                $image_arr_tmp[] = $v['image_id'];
            }

            $goods_spec_images = $image_arr_tmp;
        }
        return $goods_spec_images;
    }

    /*
     * 规格相关数据处理
     * */
    public function set_spec($type_id, $params_spec){
        $oSpec = $this->app->model('specification');
        $subSdf = array(
            'spec_value' =>array('*')
        );
        if($params_spec){
            $specifications = $oSpec->batch_dump( array('spec_id'=>array_keys($params_spec)), '*' , $subSdf, 0 ,-1 );
        }else{
            $typelist = $this->app->model('goods_type_spec')->getList('*',array('type_id'=>$type_id) );
            foreach($typelist as $row){
                $spec_ids[] =  $row['spec_id'];
            }
            $specifications = $oSpec->batch_dump( array('spec_id'=>$spec_ids), '*' , $subSdf, 0 ,-1 );
        }

        //默认规格图片
        $this->default_spec_image = $this->app->getConf('spec.default.pic');
        $this->default_spec_image_url =  base_storager::image_path($this->default_spec_image);

        //选中规格数据
        $selectSpecData = array();
        if($params_spec){
            $selectSpecData = $this->_select_spec($params_spec);
        }

        $specAll = $this->_set_spec_all($specifications, $selectSpecData);

        $aReturn = array(
            'all_spec' => $specAll,
            'selection_spec' => $selectSpecData['selectionSpec'],
        );
        return $aReturn;
    }

    /*
     * 选中的规格数据处理
     * */
    private function _select_spec($paramsSpec){

        if($this->pagedata['goods_spec_images']){
            $specGoodsImagesArr = app::get('image')->model('image')->getList('image_id,s_url,url',array('image_id'=>$this->pagedata['goods_spec_images']));
            $resource_host_url = kernel::get_resource_host_url();
            foreach($specGoodsImagesArr as $row){
                $row['s_url'] = $row['s_url'] ? $row['s_url'] : $row['url'];
                if($row['s_url'] &&!strpos($row['s_url'],'://')){
                    $row['s_url'] = $resource_host_url.'/'.$row['s_url'];
                }
                $goodsImages[$row['image_id']] = $row['s_url'];
            }
        }


        foreach((array)$paramsSpec as $specId=>$selectSpecRow)
        {
            $selectionSpec[$specId]  = $selectSpecRow;

            //当前规格选中数量
            $selectCount[$specId] = count($selectSpecRow['option']);

            //选中的规格
            foreach($selectSpecRow['option'] as $privateSpecValueId=>$option )
            {

                $selectSpecValueId[] = $option['spec_value_id'];

                unset($selectionSpec[$specId]['option'][$privateSpecValueId]);

                if( $selectSpecRow['spec_type'] == 'image' ){
                    $option['spec_image_url'] = $option['spec_image'] ? base_storager::image_path($option['spec_image']) : $this->default_spec_image_url;
                    $option['spec_image'] = $option['spec_image'] ? $option['spec_image'] : $this->default_spec_image;
                }
                $selectionSpec[$specId]['option'][$option['spec_value_id']] = $option;

                //规格关联商品图片
                $spec_goods_images = $option['spec_goods_images'] ? explode(',',$option['spec_goods_images']) : array();
                $specGoodsImages = array();
                foreach((array)$spec_goods_images as $key=>$imageId){
                    if($goodsImages[$imageId]){
                        $specGoodsImages[$key]['image_id'] = $imageId;
                        $specGoodsImages[$key]['image_url'] = $goodsImages[$imageId];
                    }
                }
                $selectionSpec[$specId]['option'][$option['spec_value_id']]['spec_goods_images'] = $specGoodsImages;
            }
        }

        return array(
            'selectionSpec' => $selectionSpec,
            'selectCount' => $selectCount,
            'selectSpecValueId' => $selectSpecValueId,
        );

    }//end function

    /*
     * 处理规格数据（在规格数据的原基础上新增是否被选中和该规格多少被选中）
     * */
    private function _set_spec_all($specifications, $selectSpecData){
        $all_spec = array();
        foreach($specifications as $key=>$row)
        {
            $all_spec[$row['spec_id']] = $row;
            $all_spec[$row['spec_id']]['selectCount'] = $selectSpecData['selectCount'][$row['spec_id']] ? $selectSpecData['selectCount'][$row['spec_id']] : 0;
            foreach( $row['spec_value'] as $spec_value_id=>$spec_value_row )
            {

                if($row['spec_type'] == 'image'){
                    $all_spec[$row['spec_id']]['spec_value'][$spec_value_id]['spec_image'] = $spec_value_row['spec_image'] ? $spec_value_row['spec_image'] : $this->default_spec_image;
                }

                $selectSpecValue = $selectSpecData['selectionSpec'][$row['spec_id']]['option'][$spec_value_id];
                if( $selectSpecValue ){
                    $all_spec[$row['spec_id']]['spec_value'][$spec_value_id]['private_spec_value_id'] = $selectSpecValue['private_spec_value_id'];
                }else{
                    $all_spec[$row['spec_id']]['spec_value'][$spec_value_id]['private_spec_value_id'] = time().$spec_value_id;
                }

                #规格中的规格值是否被选中
                if( $selectSpecData['selectSpecValueId'] &&  in_array($spec_value_id,$selectSpecData['selectSpecValueId']) )
                {
                    $all_spec[$row['spec_id']]['spec_value'][$spec_value_id]['select'] = true;
                }
                else{
                    $all_spec[$row['spec_id']]['spec_value'][$spec_value_id]['select'] = false;
                }

            }
        }
        return $all_spec;
    }

    /*
     * 获取待编辑货品
     * */
    public function getProducts($gid=0){
        if(!$gid) return false;
        $productMode = app::get('b2c')->model('products');
        $productData = $productMode->getList('product_id,bn,price,cost,mktprice,store,freez,store_place,weight,marketable,spec_desc,is_default',array('goods_id'=>$gid));
        foreach((array)$productData as $row){
            $unique_id = $this->get_unique_id($row['spec_desc']['spec_value_id']);
            $row['status'] = $row['marketable'];
            $row['freez'] = ($row['freez'] !== null) ? $row['freez'] : '0';
            $returnData[$unique_id] = $row;
        }

        return $returnData;
    }


    /*
     * 新增商品并且开启规格，保存编辑货品后（不保存商品）再编辑货品会ajax调用此方法
     *
     * */
    public function ajax_set_spec(){
        $type_id = $_GET['type_id'];
        $params_spec = json_decode($_POST['spec'],true);
        $products = json_decode($_POST['products'],true);

        //规格数据
        $result  = $this->set_spec($type_id, $params_spec);
        $this->pagedata['spec'] = $result['all_spec'];
        $this->pagedata['selection_spec'] = $result['selection_spec'];

        //生成货品的时候需要用到此数据
        $this->pagedata['selection_spec_json'] = json_encode($result['selection_spec']);

        foreach((array)$products as $uid=>$row){
            unset($products[$uid]['price']);
            $products[$uid]['price'] = $row['price']['price']['price'];
            $products[$uid]['cost'] = $row['price']['cost']['price'];
            $products[$uid]['mktprice'] = $row['price']['mktprice']['price'];
            $products[$uid]['member_lv_price'] = array();
            if($row['price']['member_lv_price']){
                foreach($row['price']['member_lv_price'] as $lv_price){
                    $products[$uid]['member_lv_price'][$lv_price['level_id']] = $lv_price['price'];
                }
            }
        }
        $this->pagedata['products'] = json_encode($products);
        echo $this->fetch('admin/goods/detail/spec/set_spec_specs.html');
    }


/*-----------------------以上为编辑货品显示数据处理函数-----------------------------*/

    /*
     * 每个货品的唯一键值(根据每个货品的规格ID生成) 在js中需要此键值来加载对应的数据
     * */
    private function get_unique_id($spec){
        $str = implode(';',$spec);
        return substr(md5($str),0,10);
    }

    /*
     * 加载页面判断是否需要有不能删除的规格和货品(对应的订单没有完成)
     * */
    private function _pre_recycle_spec($goods_id,$products){
        if(!$goods_id)  return array();
        //活动的货品
        $activeProducts = $this->_get_active_products($goods_id);

        $activeSpec = array();
        foreach($products as $uid=>$prow){
            $specValueIds = $prow['spec_desc']['spec_value_id'];
            if( in_array($prow['product_id'],$activeProducts) ){
                $activeSpec = array_merge($activeSpec,$specValueIds);
            }
        }

        $return = array(
            'activeProducts' => $activeProducts,//活动的货品ID 不能删除
            'activeSpec' => array_unique($activeSpec) //活动的规格 不能删除
        );

        return $return;
    }

    /*
     * 获取当前商品活动的货品
     * */
    private function _get_active_products($goods_id){
        //获取到当前商品的活动订单
        $ordersItemsData = $this->app->model('order_items')->getList('product_id,order_id',array('goods_id'=>$goods_id));
        foreach($ordersItemsData as $row){
            $tmpActiveProducts[$row['order_id']][] = $row['product_id'];
            $orderids[] = $row['order_id'];
        }

        $ordersData = $this->app->model('orders')->getList('order_id',array('order_id'=>$orderids,'status'=>'active'));
        $activeProducts= array();
        foreach($ordersData as $row){
            $activeProducts = array_merge($activeProducts,$tmpActiveProducts[$row['order_id']]);
        }

        $productsData = $this->app->model('products')->getList('product_id',array('goods_id'=>$goods_id));

        //如果此货品为赠品或其他service注册不能删除
        foreach( kernel::servicelist("b2c_allow_delete_goods") as $object ) {
            if( !method_exists($object,'is_delete') ) continue;
            foreach($productsData as $k=>$val){
                if( !$object->is_delete($goods_id,$val['product_id']) ){
                    $activeProducts[] = $val['product_id'];
                }
            }
        }

        //去除重复
        $activeProducts = array_unique($activeProducts);
        return $activeProducts;
    }

    /*
     * 返回错误信息
     * */
    public function result_error($code,$params){
        switch($code){
            case 1001:
                $msg = $this->app->_('货号重复 bn：').$params;
                break;
            case 1002:
                $msg = $this->app->_('货号已被其他商品使用 bn：').$params;
                break;
            case 1003:
                $msg = $this->app->_('商品价格或重量格式错误 bn：').$params;
                break;
            case 1004:
                $msg = $this->app->_('活动订单中的货品不能被删除 product_id：').$params;
                break;
            case 1005:
                $msg = $this->app->_('默认货品不可下架,修改默认货品货下架商品 bn：').$params;
                break;
            case 1006:
                $msg = $this->app->_('库存不能小于冻结库存 bn：').$params;
                break;
            default:
                $msg = $params;
                break;
        }

        echo json_encode(array('result'=>'failed', 'msg'=>$msg));
        exit;
    }


 /*--------------------------以下为保存货品函数--------------------------------------------*/

    /*
     * 保存货品入口方法
     *
     * */
    public function save_editor(){
        $goods_id = $_POST['goods']['goods_id'];
        $_POST['products'] = json_decode($_POST['products'],true);
        $_POST['spec'] = json_decode($_POST['spec'],true);

        //处理规格数据
        $selectionSpec = $this->_pre_process_spec($_POST['spec']);

        //处理货品数据
        $productsData = $this->_pre_process_products($_POST['products'],$selectionSpec); //selectionSpec传引用过滤未生成货品的规格

        //检查货品数据合法性 在检查过程中如果不通过则在此方法中直接返回错误
        $this->_check_product($goods_id,$productsData);

        //保存返回数
        $returnData = $this->_return_spec($selectionSpec);
        $returnData['productNum'] = count($productsData);
        $returnData['is_new'] = '1';

        if(!$goods_id){//新增开启规格，数据返回到新增商品页面
            foreach($_POST['spec'] as $sid=>$row){
                $returnData['spec'][$sid] = $row;
                foreach($row['option'] as $k=>$v){
                    unset($returnData['spec'][$sid]['option'][$k]);
                    $returnData['spec'][$sid]['option'][$v['private_spec_value_id']] = $v;
                }
            }

            $returnData['product'] = $productsData;
            echo json_encode(array('result'=>'success', 'data'=>$returnData, 'msg'=>app::get('b2c')->_( '操作成功' )));
            exit;
        }

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
            $olddata = app::get('b2c')->model('goods')->dump($goods_id,'goods_id',
                array('product'=>array('product_id,bn,price,cost,mktprice,store,store_place,weight,marketable,spec_desc',
                array('price/member_lv_price'=>array('*'))
            )));
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        //获取到需要删除的货品ID
        $deleteProductsId = $this->_diff_product($goods_id,$productsData);
        if($deleteProductsId){
            $this->_delete_products($deleteProductsId);
        }

        $db = kernel::database();
        $db->beginTransaction();
        //编辑的货品进行更新，新增的货品进行新增
        $flag = $this->_save_products($goods_id,$productsData);
        if(!$flag){
            $db->rollback();
            $msg = app::get('b2c')->_( '保存货品失败，请检查数据库或者保存数据' );
            $this->result_error($code,$msg);
        }

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
            if(method_exists($obj_operatorlogs,'products_log')){
                $newdata = app::get('b2c')->model('goods')->dump($goods_id,'goods_id',
                    array('product'=>array('product_id,bn,price,cost,mktprice,store,store_place,weight,marketable,spec_desc',
                    array('price/member_lv_price'=>array('*'))
                )));
                $obj_operatorlogs->products_log($newdata['product'],$olddata['product']);
            }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        //更新商品数据
        $goodsFlag = $this->_update_goods($goods_id,$productsData,$selectionSpec['spec']);
        if(!$goodsFlag){
            $db->rollback();
            $msg = app::get('b2c')->_( '更新商品数据失败，请检查数据库或者更新的数据' );
            $this->result_error($code,$msg);
        }else{
            $db->commit();
            echo json_encode(array('result'=>'success', 'data'=>$returnData, 'msg'=>app::get('b2c')->_( '保存成功' )));
            exit;
        }
    }

    /*
     * 编辑结束返回规格数据
     * */
    private function _return_spec($spec){

        foreach($spec['spec'] as $specId=>$row){
            $used_spec[] = array(
                'spec_name' => $spec['specname'][$specId],
                'nums'=>count($row)
            );
        }
        $returnData = array(
            'used_spec'=>$used_spec,
        );

        return $returnData;
    }

    /*
     * 保存编辑后的货品
     * */
    private function _save_products($goods_id,$productsData){
        $i = 1;
        $goodsData = $this->app->model('goods')->getList('name',array('goods_id'=>$goods_id));
        foreach($productsData as $row){

            if(!$row['product_id'] || $row['product_id'] == 'new'){
                unset($row['spec']);
                unset($row['product_id']);
                $row['goods_id'] = $goods_id;
                $row['name'] = $goodsData[0]['name'];
                $i++;
            }
            #else{
            #    $row['freez'] = null;//编辑货品的时候将冻结库存清空
            #}

            //编辑货品的时候如果不点击会员价并确定，则默认不提交会员价数据，则不更新会员价数据
            if(isset($row['price']['member_lv_price']) && $row['price']['member_lv_price']){
                $member_lv_price = array();
                foreach($row['price']['member_lv_price'] as $level_id=>$lv_price)
                {
                    if($lv_price){
                        $member_lv_price[] = array(
                            'level_id'=>$level_id,
                            'price'=>$lv_price,
                        );
                    }
                }
                $row['price']['member_lv_price'] = $member_lv_price;
            }
            if( !$this->app->model('products')->save($row) ){
                return false;
            }
        }
        return true;
    }

    /*
     * 更新商品数据
     * */
    private function _update_goods($goods_id,$productsData,$selectionSpec){
        $store = 0;
        foreach($productsData as $row){
            $store +=  $row['store'];
            $price[] = $row['price']['price']['price'];
        }
        $saveData = array(
            'spec_desc' => $selectionSpec,
            'price' => min($price),
            'store' => $store,
        );

        if($goods_id){
             //更新 goods_spec_index
            $filter = array(
                'goods_id' => $goods_id,
                'product' => $productsData,
            );
            $this->app->model('goods')->createSpecIndex($filter);
            //更新商品
            $flag = $this->app->model('goods')->update($saveData,array('goods_id'=>$goods_id));
        }

        kernel::single('weixin_qrcode')->update_goods_qrcode($goods_id);

        #sphinx delta
        if(kernel::single('b2c_search_goods')->is_search_status()){
            $delta = array('id'=>$goods_id,'index_name'=>'b2c_goods');
            app::get('search')->model('delta')->save($delta);
        }

        return $flag;
    }

    /*
     * 删除货品
     * */
    private function _delete_products($deleteProductsId){
        $flag = $this->app->model('products')->delete(array('product_id'=>$deleteProductsId));
        if(!$flag){
            $msg = app::get('b2c')->_( '删除货品失败' );
            $this->result_error($code,$msg);
        }
    }

    /*
     * 格式化货品数据
     * */
    private function _pre_process_products($products,&$spec){
        $tmpSpec = array();
        $tmpProducts = array();
        foreach($products as $uid=>$row)
        {
            if(!$row['bn']){//如果没有填写货号则表示删除此商品
                unset($products[$uid]);
                continue;
            }
            $tmpProducts[$uid]['product_id'] = $row['product_id'];
            $tmpProducts[$uid]['bn'] = $row['bn'];
            $tmpProducts[$uid]['weight'] = $row['weight'];
            $tmpProducts[$uid]['store_place'] = $row['store_place'];
            $tmpProducts[$uid]['store'] = $row['store'];
            $tmpProducts[$uid]['freez'] = $row['freez'];
            $tmpProducts[$uid]['is_default'] = $row['is_default'];
            $tmpProducts[$uid]['status'] = $row['status'];
            $tmpProducts[$uid]['price']['price']['price'] = $row['price'];
            $tmpProducts[$uid]['price']['cost']['price'] = $row['cost'];
            $tmpProducts[$uid]['price']['mktprice']['price'] = $row['mktprice'];
            $tmpProducts[$uid]['spec_desc'] = $row['spec_desc'];
            if($row['member_lv_price']){
                $tmpProducts[$uid]['price']['member_lv_price'] = $row['member_lv_price'];
            }

            //过滤POST提交的spec，选中规格但未生成货品,则过滤掉 |
            foreach($row['spec_desc']['spec_private_value_id'] as $specId=>$spec_private_value_id)
            {
                if( $spec['spec'][$specId][$spec_private_value_id] ){
                    $tmpSpec['spec'][$specId][$spec_private_value_id] = $spec['spec'][$specId][$spec_private_value_id];
                    $tmpSpec['spec'][$specId][$spec_private_value_id]['spec_value'] = $row['spec_desc']['spec_value'][$specId];
                }else{
                    //多个货品，但是提交过来的规格数据缺失
                    $spec_value_id = $row['spec_desc']['spec_value_id'][$specId];
                    $tmpSpec['spec'][$specId][$spec_private_value_id]['spec_value'] = $row['spec_desc']['spec_value'][$specId];
                    $tmpSpec['spec'][$specId][$spec_private_value_id]['private_spec_value_id'] = $spec_private_value_id;
                    $tmpSpec['spec'][$specId][$spec_private_value_id]['spec_value_id'] = $spec_value_id;
                    $tmpSpec['spec'][$specId][$spec_private_value_id]['spec_image'] = $spec['specValueImages'][$spec_value_id];
                    $tmpSpec['spec'][$specId][$spec_private_value_id]['spec_goods_images'] = '';
                }
            }
        }

        $tmpSpec['specname'] = $spec['specname'];
        $spec = $tmpSpec;
        $products = $tmpProducts;
        return $products;
    }

    /*
     * 对比新老货品，获取需要删除的货品
     * */
    private function _diff_product($goods_id,$products){

        $newProductsIds = array();
        foreach($products as $uid=>$row){
            if($row['product_id'] != 'new' && $row['bn']){
                $newProductsIds[] = $row['product_id'];
            }
        }

        $oldProducts = $this->app->model('products')->getList('product_id',array('goods_id'=>$goods_id));
        foreach($oldProducts as $v){
            $oldProductids[] = $v['product_id'];
        }

        $diffProducts = array_diff($oldProductids,$newProductsIds);
        return $diffProducts;
    }

    /*
     * 检查货品
     * */
    private function _check_product($goods_id,$products){
        if(!$products){
            $this->result_error($code,app::get('b2c')->_('保存数据不能为空'));
        }
        $goodsModel = $this->app->model('goods');

        $products_is_default = null;
        $listBn = array();
        foreach($products as $uid=>$row){
            if($row['product_id'] != 'new'){
                $productsIds[$row['product_id']] = $row['bn'];
            }

            if( ($row['store'] || $row['freez']) && $row['store'] < $row['freez'] ){
                $code = 1006;
                $this->result_error($code,$row['bn']);
            }

            if($row['is_default'] == 'true'){
                if(!isset($row['status']) || $row['status'] == 'false'){
                    $code = 1005;
                    $this->result_error($code,$row['bn']);
                }
                $products_is_default += 1;
            }

            if( empty($listBn) || !in_array($row['bn'],$listBn) ){
                $listBn[] = $row['bn'];
            }else{
                $code = 1001;
                $this->result_error($code,$row['bn']);
            }

            if( $goodsModel->checkProductBn($row['bn'],$goods_id ) ){
                $code = 1002;
                $this->result_error($code,$row['bn']);
            }

            if( !$goodsModel->checkPriceWeight(array($uid=>$row)) ){
                $code = 1003;
                $this->result_error($code,$row['bn']);
            }

        }//end foreach

        if(is_null($products_is_default) ){
            $msg = app::get('b2c')->_( '请选择默认货品' );
            $this->result_error($code,$msg);
        }
        if($products_is_default > 1){
            $msg = app::get('b2c')->_( '只能选择一个默认货品' );
            $this->result_error($code,$msg);
        }


        $activeProducts = $this->_get_active_products($goods_id);
        foreach($activeProducts  as $pid){
            if(!$productsIds[$pid]){
                $code = 1004;
                $this->result_error($code,$pid);
            }
        }

        return true;
    }

    /*
     * 处理POST的选中的spec数据
     * */
    private function _pre_process_spec($spec){
        if( empty($spec) ){
            return app::get('b2c')->_('未选择规格，请选择规格');
        }

        foreach((array)$spec as $specId=>$specRow)
        {
            $tmpspecRow = array();
            foreach($specRow['option'] as $k=>$optionRow)
            {
                unset($specRow['option'][$k]);
                unset($optionRow['spec_image_url']);

                $privateSpecValueId = $optionRow['private_spec_value_id'];
                $optionRow['spec_goods_images'] = $this->_pre_process_spec_goods_images($optionRow['spec_goods_images']);
                $tmpspecRow[$privateSpecValueId] = $optionRow;
            }

            if($spec[$specId]['spec_type'] == 'image'){
                $specIds[] = $specId;
            }

            $spec[$specId] = $tmpspecRow;
            $specname[$specId] = $specRow['spec_name'];
        }

        $specValueData = $this->app->model('spec_values')->getList('spec_value_id,spec_image',array('spec_id'=>$specIds));
        foreach($specValueData as $row){
            $specValueImages[$row['spec_value_id']] = $row['spec_image'];
        }

        $data = array(
            'spec' => $spec,
            'specname' => $specname,
            'specValueImages' => $specValueImages,
        );
        return $data;
    }

    /*
     * 处理规格的关联商品图片数据
     * */
    private function _pre_process_spec_goods_images($spec_goods_images){

      if( empty($spec_goods_images) ) return '';

      $goodsImages = array();
      foreach($spec_goods_images as $row){
        $goodsImages[] =  $row['image_id'];
      }

      $goodsImagesStr = implode(',',$goodsImages);
      return $goodsImagesStr;
    }//end function
}
