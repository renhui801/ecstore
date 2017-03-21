<?php

class b2c_apiv_apis_20_ome_aftersales extends b2c_apiv_extends_request{
    var $method = 'store.trade.aftersale.add';
    var $callback = array();
    var $title = '新建售后申请';
    var $timeout = 1;
    var $async = true;

    public function get_params($sdf){
       $arr_data = array();
        $arr_data['tid'] = $sdf['order_id'];
        $arr_data['aftersale_id'] = $sdf['return_id'];
        if ($sdf['title'])
            $arr_data['title'] = $sdf['title'];
        if ($sdf['content'])
            $arr_data['content'] = $sdf['content'];
        $arr_data['messager'] = '';
        if ($sdf['add_time'])
            $arr_data['created'] = date('Y-m-d H:i:s', $sdf['add_time']);
        if ($sdf['comment'])
            $arr_data['memo'] = $sdf['comment'] ? $sdf['comment'] : '';
        if ($sdf['status'])
            $arr_data['status'] = $sdf['status'];
        if ($sdf['member_id'])
            $arr_data['buyer_id'] = $sdf['member_id'];
        if ($sdf['product_data'])
            $arr_product_data = unserialize($sdf['product_data']);

        if ($sdf['image_file'])
        {
            $arr_data['attachment'] = base_storager::image_path($sdf['image_file']);
        }

        if (isset($arr_product_data) && $arr_product_data)
        {
            foreach ($arr_product_data as $key=>&$items)
            {
                $arr_product_data[$key]['sku_bn'] = $items['bn'];
                unset($items['bn']);
                $arr_product_data[$key]['sku_name'] = $items['name'];
                unset($items['name']);
                $arr_product_data[$key]['number'] = $items['num'];
                unset($items['num']);
            }

            $arr_data['aftersale_items'] = json_encode($arr_product_data);
        }
        else
        {
            $arr_data['aftersale_items'] = "";
        }
        return $arr_data;
        
    }
}

