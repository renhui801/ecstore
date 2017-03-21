<?php

class b2c_apiv_apis_response_goods_type{

    public $app;
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 根据分类获取类型及其详情
     * @param $type_id
     * return $goods_type_detial
     */
    public function get_type_detial($param,&$service){
        if(empty($param['type_id']))
            $service->send_user_error('7001', '商品类型id不能为空！');
        //获取商品类型model
        $goods_type=$this->app->model('goods_type');
        $goods_type_detial=$goods_type->getRow('type_id,name,alias,price,tab,params,setting',array('type_id'=>$param['type_id']));
        if($goods_type_detial['params']){
            $goods_type_detial['params'] = unserialize($goods_type_detial['params']);
        }
        //return  $goods_type_detial;

        //获取关联商品品牌
        $goods_brand=$this->app->model('type_brand');
        $brand=$this->app->model('brand');
        $brand_id=$goods_brand->getList('brand_id',array('type_id'=>$param['type_id']));
        foreach ($brand_id as $key => $value) {
            foreach ($value as $k => $v) {
                $brand_id[$key]=$v['brand_id'];
            }
        }
        $brand_detial=$brand->getList('brand_name,brand_id',array('brand_id|in'=>$brand_id));
        //return $brand_detial;

        //获取商品规格
        $goods_type_spec=$this->app->model('goods_type_spec');
        $spec_name=$this->app->model('specification');
        $spec_values=$this->app->model('spec_values');
        $spec_id=$goods_type_spec->getList('spec_id,spec_style',array('type_id'=>intval($param['type_id'])));
        //return $spec_id;
        foreach ($spec_id as $key => $value) {
           $fmt_id[$value['spec_id']]['spec_id']=$value['spec_id'];
           $fmt_id[$value['spec_id']]['spec_style']=$value['spec_style'];
        }
        //return $fmt_id;
        foreach ($spec_id as $key => $value) {
            $spec_id[$key]=$value['spec_id'];
        }
        $spec_name=$spec_name->getList('spec_id,spec_name',array('spec_id|in'=>$spec_id));
        
        foreach ($spec_name as $key => $value) {
            $fmt_name[$value['spec_id']]['spec_id']=$value['spec_id'];
            $fmt_name[$value['spec_id']]['spec_name']=$value['spec_name'];
            $fmt_name[$value['spec_id']]['spec_style']=$fmt_id[$value['spec_id']]['spec_style'];
        }
        //return  $fmt_name;
        
        $spec_value =$spec_values->getList('spec_id,spec_value,spec_value_id',array('spec_id|in'=>$spec_id));
        // /return $spec_value;
        foreach ($spec_value as $key => $value) {
            $fmt_spec[$value['spec_id']]['spec_id'] = $value['spec_id'];
            $fmt_spec[$value['spec_id']]['spec_name'] = $fmt_name[$value['spec_id']]['spec_name'];
            $fmt_spec[$value['spec_id']]['spec_style'] = $fmt_name[$value['spec_id']]['spec_style'];
            $fmt_spec[$value['spec_id']]['spec_value'][$value['spec_value_id']]['spec_value'] = $value['spec_value'];
            $fmt_spec[$value['spec_id']]['spec_value'][$value['spec_value_id']]['spec_value_id'] = $value['spec_value_id'];
        }
        //return  $fmt_spec;


        //获取类型扩展属性
        $goods_type_props=$this->app->model('goods_type_props');
        $goods_type_props_value=$this->app->model('goods_type_props_value');
        $props_value=$goods_type_props->getList('props_id,search,show,name',array('type_id'=>$param['type_id']));
        foreach ($props_value as $key => $value) {
            $props_id[$key]=$value['props_id'];
        }
        //return $props_id;
        //return $props_value;
        foreach ($props_value as $key => $value) {
           $fmt_value[$value['props_id']]['props_id']=$value['props_id'];
           $fmt_value[$value['props_id']]['search']=$value['search'];
           $fmt_value[$value['props_id']]['show']=$value['show'];
           $fmt_value[$value['props_id']]['name']=$value['name'];
        }
        //return $fmt_value;
        $props_values=$goods_type_props_value->getList('props_id,name,props_value_id',array('props_id|in'=>$props_id));
        foreach ($props_values as $key => $value) {
            $fmt_props[$value['props_id']]['props_id'] = $value['props_id'];
            $fmt_props[$value['props_id']]['props_name'] = $fmt_value[$value['props_id']]['name'];
            $fmt_props[$value['props_id']]['search'] = $fmt_value[$value['props_id']]['search'];
            $fmt_props[$value['props_id']]['show'] = $fmt_value[$value['props_id']]['show'];
            $fmt_props[$value['props_id']]['props_values'][$value['props_value_id']]['name'] = $value['name'];
            $fmt_props[$value['props_id']]['props_values'][$value['props_value_id']]['props_value_id'] = $value['props_value_id'];
        }
        //return $props_values;
        //return $fmt_props;

        //最后返回数据
        $goods_type_detial['props_values']=$fmt_props;
        $goods_type_detial['spec_value']=$fmt_spec;
        $goods_type_detial['brand_detial']=$brand_detial;

        return $goods_type_detial;
    }
}
