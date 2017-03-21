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
class timedbuy_ctl_site_timedbuy extends b2c_frontpage
{
    
    function __construct( &$app )
    {
        $this->app = $app;
        parent::__construct( $app );
    }
    
    public function request_time_now() {
        echo time();exit;
    }
    
    public function get_goods_spec() {
        $gid = $this->_request->get_get('gid');
        if( !$gid ) {
            echo '';
            exit;
        }
        $this->pagedata['goodshtml']['name'] = kernel::single("b2c_goods_detail_name")->show( $gid,$arrGoods );
        if( $arrGoods['spec'] && is_array($arrGoods['spec']) )  {
            foreach( $arrGoods['spec'] as $row ) {
                $option = $row['option'];
                if( $option && is_array($option) ) {
                    foreach( $option as $img ) {
                        foreach( (array)explode(',',$img['spec_goods_images']) as $imageid )
                            $return[$imageid] = base_storager::image_path($imageid,'s');
                    }
                }
            }
        }
        $this->pagedata['spec2image'] = json_encode( $return );

        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['defaultImage'] = $imageDefault['S']['default_image'];

        $arrGoods['spec2image'] = json_encode($return);
        $this->pagedata['goods'] = $arrGoods;
        $this->pagedata['goodshtml']['spec'] = kernel::single("b2c_goods_detail_spec")->show( $gid,$arrGoods );
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['S']['default_image'];
        $this->pagedata['goodshtml']['button'] = kernel::single('b2c_goods_detail_button')->show( $gid,$arrGoods );
        $this->pagedata['form_url'] = $this->gen_url( array('app'=>'b2c','ctl'=>'site_cart','act'=>'add','arg0'=>'goods','arg1'=>'quick') );
        
        $this->page( 'site/gallery/spec_dialog.html',true );
    }

    function request_widget_data(){
        $rule_id = $_REQUEST['rule_id'];
        $goods_id = $_REQUEST['goods_id'];
        $timedbuyObj = app::get('timedbuy')->model('objitems');
        $allinfo = $timedbuyObj->getList('*',array('sales_rule_id'=>$rule_id,'goods_id'=>$goods_id));
        $buywquantity = 0;
        foreach ($allinfo as $key => $value) {
            $buywquantity+=$value['quantity'];
        }
        $arr_sales_info = kernel::single('timedbuy_info')->get_sales_goods_info( $goods_id );
        $config_basicinfo = unserialize($arr_sales_info['action_solution']);
        $config_quantity = $config_basicinfo['timedbuy_promotion_solution_timedbuy']['quantity'];
        $canbuynum = $config_quantity-$buywquantity;
        echo json_encode(array('timeNow'=>time(),'inventory'=>$canbuynum));exit;
    }

}