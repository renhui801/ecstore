<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

//*******************************************************************
//  凑单功能控制器
//*******************************************************************
class b2c_ctl_admin_sales_fororder extends desktop_controller{

    public $workground = 'b2c_ctl_admin_sales_fororder';

    public function index(){

        $fororder = app::get('b2c')->getConf('cart_fororder_setting');
        if(empty($fororder)){
            $fororder['fororder']['show'] = 'true';
            $fororder['fororder']['nums'] = '40';
            $fororder['fororder']['filter'] = array(
                array('price_min'=>0,'price_max'=>20),array('price_min'=>20,'price_max'=>50),array('price_min'=>50,'price_max'=>100)
            );
            app::get('b2c')->setConf('cart_fororder_setting',$fororder);
        }

        if($fororder['fororder']['filter']){
            foreach($fororder['fororder']['filter'] as $key=>$value){
                kernel::single('b2c_ctl_site_cart')->_check_fororder($value,$nums);
                $fororder['fororder']['filter'][$key]['goods_nums'] = $nums;
            }
        }

        $this->pagedata['status'] = array( 'true'=>'是','false'=>'否' );
        $this->pagedata['fororder'] = $fororder['fororder'];
        $view = 'admin/sales/fororder.html';
        $this->page($view);
    }

    public function save_setting(){
        $this->begin();
        if(empty($_POST)) $this->end(false,app::get('b2c')->_('请填写配置信息'));

        if($_POST['fororder']['filter']){
            $filter = array();
            foreach($_POST['fororder']['filter'] as $key=>$value){
                if(isset($value['price_max'])){
                    $filter[$key-1]['price_max'] = $value['price_max']?$value['price_max'] : 999999;
                    if($filter[$key-1]['price_min'] > $filter[$key-1]['price_max'])
                        $this->end(false,app::get('b2c')->_('上限值必须大于下限值'));
                }else{
                    $filter[$key]['price_min'] = $value['price_min'] ? $value['price_min'] : 0;
                }

            }
        }

        $setting = app::get('b2c')->getConf('cart_fororder_setting');
        $setting['fororder']['show'] = $_POST['fororder']['show'];
        $setting['fororder']['nums'] = $_POST['fororder']['nums']?$_POST['fororder']['nums'] : 40;
        $setting['fororder']['filter'] = $filter ? $filter : $setting['fororder']['filter'];
        app::get('b2c')->setConf('cart_fororder_setting',$setting);
        $this->end(true, app::get('b2c')->_('当前配置修改成功！'));
    }

    public function check(){
        $filter['price_min'] = $_POST['fororder']['filter'][0]['price_min'];
        $filter['price_max'] = $_POST['fororder']['filter'][1]['price_max'];
        if($filter['price_min'] < 0 || $filter['price_max'] < 0  || $filter['price_min'] > $filter['price_max']){
            echo json_encode(array('success'=>"<span class='c-red'>上限值必须大于下限值</span>"));
            exit;
        }

        kernel::single('b2c_ctl_site_cart')->_check_fororder($filter,$msg);
        echo json_encode(array('success'=>"该区间的商品数量为<span class='c-red'>".$msg."</span>件"));
        exit;
    }

}
