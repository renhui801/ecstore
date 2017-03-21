<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * 商品详情页分享组件
 */

class b2c_goods_share {

    function get_share($aGoods){
        $data = $this->get_share_goods_data($aGoods);
        $share_api = app::get('b2c')->model('goods_share')->getList('*',array('status'=>1),0,-1,'order_by');
        $goods_share = array();
        foreach($share_api as $key=>$share_row){
            $share_url = $this->get_share_url($share_row,$data);
            $goods_share[$key]['key'] = $share_row['name'];
            $goods_share[$key]['url'] = $share_url['url'];
            $goods_share[$key]['name'] = $share_url['name'];
        }
        return $goods_share;
    }

    function get_share_goods_data($aGoods){
        if($aGoods['images']){
            foreach($aGoods['images'] as $images_row){
                $image_url = base_storager::image_path($images_row['image_id']);
                $images[] = $image_url;
            }
        }
        $data['title'] = $aGoods['title'];
        $data['price'] = $aGoods['price'];
        $data['image'] = implode(',',$images);

        $url_array = array(
            'app'=>'b2c',
            'ctl'=>'site_product',
            'arg0'=>$aGoods['product_id'],
        );
        $product_url = kernel::single('base_component_request')->get_full_http_host().kernel::single('site_controller')->gen_url($url_array);
        $data['link'] = $product_url;
        $data['shopname'] = app::get('site')->getConf('site.name');
        return $data;
    }

    function get_share_url($row,$data){
        $title = '【'.$data['title'].'】，销售价'.$data['price'].'（分享自 '.$data['shopname'].'）';
        $name = $row['name'];
        switch($name){
            case 'sina':
                    //$params['resourceUrl'] = 'index.php';
                    $params['srcUrl'] = $data['link'];
                    $params['pic'] = $data['image'];
                    $params['title'] = $title;
                    $params['appkey'] = $row['appkey'];
                    $share_name = app::get('b2c')->_('新浪微博');
                break;
            case 'tencent':
                    $params['c'] = 'share';
                    $params['a'] = 'index';
                    $params['site'] = $data['link'];
                    $params['pic'] = $data['image'];
                    $params['title'] = $title;
                    $params['appkey'] = $row['appkey'];
                    $share_name = app::get('b2c')->_('腾讯微博');
                break;
             case 'qzone':
                    $params['url'] = $data['link'];
                    $params['title'] = $title;
                    $params['pics'] = $data['image'];
                    $share_name = app::get('b2c')->_('QQ空间');
                break;
            case 'renren':
                    $params['resourceUrl'] = $data['link'];
                    $params['pic'] = $data['image'];
                    $params['title'] = $title;
                    $share_name = app::get('b2c')->_('人人网');
                break;
            case 'kaixin001':
                    $params['flag'] = 1;
                    $params['style'] = 11;
                    $params['url'] = $data['link'];
                    $params['pic'] = $data['image'];
                    $params['content'] = $title;
                    $share_name = app::get('b2c')->_('开心网');
                break;
            case 'douban':
                    $params['href'] = $data['link'];
                    $params['image'] = $data['image'];
                    $params['name'] = $title;
                    $share_name = app::get('b2c')->_('豆瓣网');
                break;
        }
        if($params){
            foreach($params as $key=>$value){
                $params_url .= $key.'='.urlencode($value).'&';
            }
        }
        $share_url['url'] = $row['api'].'?'.$params_url;
        $share_url['name'] = $share_name;
        return $share_url;
    }
}
