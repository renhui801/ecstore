<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class goodsapi_shopex_spec_values_list extends goodsapi_goodsapi{

    public function __construct(){
        parent::__construct();
        $this->spec_model = app::get('b2c')->model('specification');
        $this->spec_values_model = app::get('b2c')->model('spec_values');
    }

    //获取商品规格列表接口
    function shopex_spec_values_list($params){
        $params = $this->params;
        //api 调用合法性检查
        $this->check($params);

        /*如果当前用户不是系统管理员，检查当前用户操作权限（暂时不限制权限）
        if( !$this->is_admin )
            $this->user_permission($this->user_id,'catgoods');
        */
        $params['page_no'] = isset($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = isset($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $page_size = intval($params['page_size']);
        $page_offset = $page_no * $page_size;

        $spec_row = $this->spec_model->getList('spec_id',array('spec_name'=>$params['spec_names'],'alias'=>$params['spec_alias']));
        if($spec_row){
            $spec_id = $spec_row[0]['spec_id'];
            $filter['spec_id'] = $spec_id;
        }else{
            $error['code'] = null;
            $error['msg'] = 'ECStore中没有对应的规格';
            $this->send_error($error);
        }
        if($params['page_no'] == -1){
            $item_total = $this->spec_values_model->count($filter);
            $data['item_total'] = $item_total;
            $this->send_success($data);
        }else{
            $item_total = $this->spec_values_model->count($filter);
            $spec_values = $this->spec_values_model->getList('*',$filter,$page_offset,$page_size);
        }

        if( !$spec_values){
            $this->send_success();
        }

        //获取规格值
        foreach( $spec_values as $spec_k=>$spec_v){
            $image_url = base_storager::image_path($spec_v['spec_image']);
            $spec_value[$spec_k] = array(
                'spec_value' =>$spec_v['spec_value'],
                'new_spec_value' => '',
                'spec_value_alias' => $spec_v['alias'],
                'order_by' => intval($spec_v['p_order']),
                'image_url' => substr($image_url,0,-13),
            );
        }

        $data = $spec_value;
        $data['item_total'] = $item_total;
        $this->send_success($data);
    }
}
