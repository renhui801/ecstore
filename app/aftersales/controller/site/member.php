<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class aftersales_ctl_site_member extends b2c_ctl_site_member
{
    /**
     * 构造方法
     * @param object application
     */
    public function __construct(&$app)
    {
        $this->app_current = $app;
        $this->app_b2c = app::get('b2c');
        parent::__construct($this->app_b2c);
    }

    /*
     *退换货记录
     * */
    public function afterrec($type='noarchive', $nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('退换货记录'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;

        $filter = array();
        $filter["member_id"] = $this->app_b2c->member_id;
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();
        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if($type == 'archive'){
            $this->pagedata['type']='archive';
            $aData = $this->get_return_product_list('*', $filter, $nPage);
        }else{
            $this->pagedata['type']='noarchive';
            $aData = $obj_return_policy->get_return_product_list('*', $filter, $nPage);
        }
        foreach($aData['data'] as $key=>$val){
            $aData['data'][$key]['product_data'] = unserialize($val['product_data']);
            $aData['data'][$key]['comment'] = unserialize($val['comment']);
        }
        if (isset($aData['data']) && $aData['data'])
            $this->pagedata['return_list'] = $aData['data'];

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];
        $arrPager = $this->get_start($nPage, $aData['total']);
        $this->pagination($nPage, $arrPager['maxPage'], 'afterrec', array($type), 'aftersales', 'site_member');
        $this->pagedata['controller'] = 'afterlist';

        $this->output('aftersales');
    }

    /**
     * 得到满足条件的售后申请列表
     * @param string database table columns
     * @param array conditions
     * @param int page code
     * @return array 结果数组
     */
    public function get_return_product_list($clos='*', $filter = array(), $nPage=1)
    {
        $arr_return_products = array();
        $oArchiveRProduct = app::get('aftersales')->model('archive_return_product');
        $aData = $oArchiveRProduct->getList($clos,$filter,($nPage-1)*10,10,'add_time DESC');

        $count = $oArchiveRProduct->count($filter);

        return $arr_return_products = array(
            'data' => $aData,
            'total' => $count,
        );
    }

    /*
     * 申请退换货列表
     * @params int $nPage 页码
     * */
    public function afterlist($nPage=1)
    {
        $this->path[] = array('title'=>app::get('b2c')->_('会员中心'),'link'=>$this->gen_url(array('app'=>'b2c', 'ctl'=>'site_member', 'act'=>'index','full'=>1)));
        $this->path[] = array('title'=>app::get('b2c')->_('申请退换货'),'link'=>'#');
        $GLOBALS['runtime']['path'] = $this->path;
        $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        $order = $this->app->model('orders');

        $order_status['pay_status'] = 1;
        $order_status['ship_status'] = 1;

        $aData = $order->fetchByMember($this->app_b2c->member_id,$nPage-1,$order_status,10);

        $this->get_order_details($aData,'member_orders');
        $oImage = app::get('image')->model('image');
        $oGoods = app::get('b2c')->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');

        foreach($aData['data'] as $k => &$v) {
            foreach($v['goods_items'] as $k2 => &$v2) {
                $spec_desc_goods = $oGoods->getList('spec_desc,image_default_id',array('goods_id'=>$v2['product']['goods_id']));
                if($v2['product']['products']['spec_desc']['spec_private_value_id']){
                    $select_spec_private_value_id = reset($v2['product']['products']['spec_desc']['spec_private_value_id']);
                    $spec_desc_goods = reset($spec_desc_goods[0]['spec_desc']);
                }
                if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                    list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                    $v2['product']['thumbnail_pic'] = $default_product_image;
                }elseif($spec_desc_goods[0]['image_default_id']){
                    if( !$v2['product']['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$spec_desc_goods[0]['image_default_id']))){
                        $v2['product']['thumbnail_pic'] = $imageDefault['S']['default_image'];
                    }else{
                        $v2['product']['thumbnail_pic'] = $spec_desc_goods[0]['image_default_id'];
                    }
                }
            }
        }
        $this->pagedata['orders'] = $aData['data'];

        $this->pagination($nPage, $aData['pager']['total'], 'afterlist', '', 'aftersales', 'site_member');

        $this->output('aftersales');
    }


    /**
     *售后须知页面
     */
    public function read(){
        $this->pagedata['comment'] = app::get('aftersales')->getConf('site.return_product_comment');
        $this->display('site/member/read.html','aftersales');
    }

    public function add($order_id)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"),false,true);
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"),false,true);
        }

        $objOrder = $this->app_b2c->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $this->pagedata['order'] = $objOrder->dump($order_id, '*', $subsdf);

        // 校验订单的会员有效性.
        $is_verified = ($this->_check_verify_member($this->pagedata['order']['member_id'])) ? $this->_check_verify_member($this->pagedata['order']['member_id']) : false;

        // 校验订单的有效性.
        if ($_COOKIE['ST_ShopEx-Order-Buy'] != md5($this->app->getConf('certificate.token').$order_id) && !$is_verified)
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'),false,true);
        }

        $this->pagedata['orderlogs'] = $objOrder->getOrderLogList($order_id);

        if(!$this->pagedata['order'])
        {
            $this->end(false,  app::get('b2c')->_('订单无效！'), array('app'=>'site','ctl'=>'default','act'=>'index'),false,true);
        }

        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $objMath = kernel::single("ectools_math");
        $oImage = app::get('image')->model('image');
        $oGoods = app::get('b2c')->model('goods');
        $imageDefault = app::get('image')->getConf('image.set');
        foreach ($this->pagedata['order']['order_objects'] as $k=>$arrOdr_object)
        {
            $index = 0;
            $index_adj = 0;
            $index_gift = 0;
            $tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods')
            {
                foreach($arrOdr_object['order_items'] as $key => $item)
                {
                    if ($item['item_type'] == 'product')
                        $item['item_type'] = 'goods';
                    if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
                        $tmp_array = (array)$tmp_array;
                        if (!$tmp_array) continue;

                        $product_id = $tmp_array['products']['product_id'];
                        if (!$order_items[$product_id]){
                            $tmp_array['arrNum'] = $this->intArray($tmp_array['quantity']);
                            $order_items[$product_id] = $tmp_array;
                        }else{
                            $order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
                            $order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
                            $order_items[$product_id]['arrNum'] = $this->intArray($order_items[$product_id]['quantity']);
                        }
                        // 货品图片
                        $spec_desc_goods = $oGoods->getList('spec_desc,image_default_id',array('goods_id'=>$item['goods_id']));
                        if($item['products']['spec_desc']['spec_private_value_id']){
                            $select_spec_private_value_id = reset($item['products']['spec_desc']['spec_private_value_id']);
                            $spec_desc_goods = reset($spec_desc_goods[0]['spec_desc']);
                        }
                        if($spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']){
                            list($default_product_image) = explode(',', $spec_desc_goods[$select_spec_private_value_id]['spec_goods_images']);
                            $order_items[$product_id]['thumbnail_pic'] = $default_product_image;
                        }elseif($spec_desc_goods[0]['image_default_id']){
                            if( !$order_items[$product_id]['thumbnail_pic'] && !$oImage->getList("image_id",array('image_id'=>$spec_desc_goods[0]['image_default_id']))){
                                $order_items[$product_id]['thumbnail_pic'] = $imageDefault['S']['default_image'];
                            }else{
                                $order_items[$product_id]['thumbnail_pic'] = $spec_desc_goods[0]['image_default_id'];
                            }
                        }
                        //$order_items[$item['products']['product_id']] = $tmp_array;
                    }
                }
            }
            else
            {
                if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object))
                {
                    $tmp_array = (array)$tmp_array;
                    if (!$tmp_array) continue;
                    foreach ($tmp_array as $tmp){
                        if (!$order_items[$tmp['product_id']]){
                            $tmp['arrNum'] = $this->intArray($tmp['quantity']);
                            $order_items[$tmp['product_id']] = $tmp;
                        }else{
                            $order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
                            $order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
                            $order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
                            $order_items[$tmp['product_id']]['arrNum'] = $this->intArray($order_items[$tmp['product_id']]['quantity']);
                        }
                    }
                }
                //$order_items = array_merge($order_items, $tmp_array);
            }
        }

        $this->pagedata['order_id'] = $order_id;
        $this->pagedata['order']['items'] = $order_items;
        $this->pagedata['controller'] = 'afterlist';
        // echo "<pre>";print_r($this->pagedata);exit;
        $this->output('aftersales');
    }

    private function intArray($int=1){
        for($i=1;$i<=$int;$i++){
            $return[$i] = $i;
        }
        return $return;
    }

    /**
     * 截取文件名不包含扩展名
     * @param 文件全名，包括扩展名
     * @return string 文件不包含扩展名的名字
     */
    private function fileext($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

    /*
     *无刷新上传图片，返回信息
     * */
    public function ajax_callback($status='error',$msg,$url=''){
        header('Content-Type:text/html; charset=utf-8');
        echo '<script>parent.ajax_callback("'.$status.'","'.$msg.'","'.$url.'")</script>';exit;
    }

    public function return_save()
    {
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->ajax_callback('error',app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->ajax_callback('error',app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        if (!$_POST['product_bn'])
        {
            $this->ajax_callback('error',app::get('aftersales')->_("您没有选择商品，请先选择商品！"));
        }

        if (!$_POST['title'])
        {
            $this->ajax_callback('error',app::get('aftersales')->_("请填写退货理由"));
        }

        $upload_file = "";
        if ( $_FILES['file']['size'] > 314572800 )
        {
            $this->ajax_callback('error',app::get('aftersales')->_("上传文件不能超过300M！"));
        }

        if ( $_FILES['file']['name'] != "" )
        {
            $type=array("png","jpg","gif","jpeg","rar","zip");

            if(!in_array(strtolower($this->fileext($_FILES['file']['name'])), $type))
            {
                $text = implode(",", $type);
                $this->end(false, app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>", $com_url,false,$_POST['response_json']);
                $this->ajax_callback('error',app::get('aftersales')->_("您只能上传以下类型文件: ") . $text . "<br>");
            }

            $mdl_img = app::get('image')->model('image');
            $image_name = $_FILES['file']['name'];
            $image_id = $mdl_img->store($_FILES['file']['tmp_name'],null,null,$image_name);
            $mdl_img->rebuild($image_id,array('L','M','S'));

            if (isset($_REQUEST['type']))
            {
                $type = $_REQUEST['type'];
            }
            else
            {
                $type = 's';
            }
            $image_src = base_storager::image_path($image_id, $type);
        }

        if(!$_POST['agree']){
            $this->ajax_callback('error',app::get('aftersales')->_("请先查看售后服务须知并且同意"));
        }

        $obj_filter = kernel::single('b2c_site_filter');
        $_POST = $obj_filter->check_input($_POST);

        $product_data = array();
        foreach ((array)$_POST['product_bn'] as $key => $val)
        {
            $item = array();
            $item['bn'] = $val;
            $item['name'] = $_POST['product_name'][$key];
            $item['num'] = intval($_POST['product_nums'][$key]);
            $item['price'] = floatval($_POST['product_price'][$key]);
            $product_data[] = $item;
        }

        $aData['order_id'] = $_POST['order_id'];
        $aData['title'] = $_POST['title'];
        $aData['type'] = $_POST['type']==2 ? 2 : 1;
        $aData['add_time'] = time();
        $aData['image_file'] = $image_id;
        $aData['member_id'] = $this->app_b2c->member_id;
        $aData['product_data'] = serialize($product_data);
        $aData['content'] = $_POST['content'];
        $aData['status'] = 2;

        $msg = "";
        $obj_aftersales = kernel::service("api.aftersales.request");
        if ($obj_aftersales && $obj_aftersales->generate($aData, $msg))
        {
            $obj_rpc_request_service = kernel::service('b2c.rpc.send.request');
            if ($obj_rpc_request_service && method_exists($obj_rpc_request_service, 'rpc_caller_request'))
            {
                if ($obj_rpc_request_service instanceof b2c_api_rpc_request_interface)
                    $obj_rpc_request_service->rpc_caller_request($aData,'aftersales');
            }
            else
            {
                $obj_aftersales->rpc_caller_request($aData);
            }

            $this->ajax_callback('success',app::get('aftersales')->_("提交成功"),$this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'afterrec')));
        }
        else
        {
            $this->ajax_callback('error',$msg);
        }
    }

    public function return_details($return_id)
    {
        $this->begin($this->gen_url(array('app' => 'b2c', 'ctl' => 'site_member')));
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $arr_settings = array();

        if (!isset($obj_return_policy) || !is_object($obj_return_policy))
        {
            $this->end(false, app::get('aftersales')->_("售后服务应用不存在！"));
        }

        if (!$obj_return_policy->get_conf_data($arr_settings))
        {
            $this->end(false, app::get('aftersales')->_("售后服务信息没有取到！"));
        }

        $this->pagedata['return_item'] =  $obj_return_policy->get_return_product_by_return_id($return_id);
        $this->pagedata['return_id'] = $return_id;
        if( !($this->pagedata['return_item']) )
        {
           $this->begin($this->gen_url(array('app' => 'aftersales', 'ctl' => 'site_member', 'act' => 'return_list')));
           $this->end(false, $this->app->_("售后服务申请单不存在！"));
        }

        $this->output('aftersales');
    }

    /**
     * 下载售后附件
     * @param string return id
     * @return null
     */
    public function file_download($return_id)
    {
        $obj_return_policy = kernel::service("aftersales.return_policy");
        $obj_return_policy->file_download($return_id);
    }

    public function return_order_items($order_id){
        $objOrder = $this->app_b2c->model('orders');
        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*',array(':products'=>'*')))));
        $orderinfo = $objOrder->dump($order_id, '*', $subsdf);

        $order_items = array();
        // 所有的goods type 处理的服务的初始化.
        $arr_service_goods_type_obj = array();
        $arr_service_goods_type = kernel::servicelist('order_goodstype_operation');
        foreach ($arr_service_goods_type as $obj_service_goods_type)
        {
            $goods_types = $obj_service_goods_type->get_goods_type();
            $arr_service_goods_type_obj[$goods_types] = $obj_service_goods_type;
        }

        $objMath = kernel::single("ectools_math");
        foreach ($orderinfo['order_objects'] as $k=>$arrOdr_object)
        {
            $tmp_array = array();
            if($arrOdr_object['obj_type'] == 'timedbuy'){
                $arrOdr_object['obj_type'] = 'goods';
            }
            if ($arrOdr_object['obj_type'] == 'goods'){
                foreach($arrOdr_object['order_items'] as $key => $item){
                    if ($item['item_type'] == 'product'){
                        $item['item_type'] = 'goods';
                    }

                    if ($tmp_array = $arr_service_goods_type_obj[$item['item_type']]->get_aftersales_order_info($item)){
                        $tmp_array = (array)$tmp_array;
                        if (!$tmp_array) continue;

                        $product_id = $tmp_array['products']['product_id'];
                        if (!$order_items[$product_id]){
                            $order_items[$product_id] = $tmp_array;
                        }else{
                            $order_items[$product_id]['sendnum'] = floatval($objMath->number_plus(array($order_items[$product_id]['sendnum'],$tmp_array['sendnum'])));
                            $order_items[$product_id]['quantity'] = floatval($objMath->number_plus(array($order_items[$product_id]['quantity'],$tmp_array['quantity'])));
                        }
                    }
                    unset($order_items[$product_id]['products']);
                }
            }else{
                if ($tmp_array = $arr_service_goods_type_obj[$arrOdr_object['obj_type']]->get_aftersales_order_info($arrOdr_object)){
                    $tmp_array = (array)$tmp_array;
                    if (!$tmp_array) continue;
                    foreach ($tmp_array as $tmp){
                        if (!$order_items[$tmp['product_id']]){
                            $order_items[$tmp['product_id']] = $tmp;
                        }else{
                            $order_items[$tmp['product_id']]['sendnum'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['sendnum'],$tmp['sendnum'])));
                            $order_items[$tmp['product_id']]['nums'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['nums'],$tmp['nums'])));
                            $order_items[$tmp['product_id']]['quantity'] = floatval($objMath->number_plus(array($order_items[$tmp['product_id']]['quantity'],$tmp['quantity'])));
                        }
                        unset($order_items[$tmp['product_id']]['products']);
                    }
                }
            }
        }

        return $order_items;
    }


}
