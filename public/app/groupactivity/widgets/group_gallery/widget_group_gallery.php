<?php
function widget_group_gallery(&$setting,&$smarty){
    $goods_list = json_decode($setting['goods'],1);
    $goodsId = array();$goodsInfo = array();
    if (is_array($goods_list))
    foreach ($goods_list as $goods){
        $image_id = app::get('b2c')->model('goods')->getList('image_default_id',array('goods_id'=>$goods['id']));
        $goods['pic'] = $image_id[0]['image_default_id']; 
        $goodsId[] = $goods['id'];
        $goodsInfo[$goods['id']] = $goods;
    }
    $filter['goodsId'] = $goodsId;
    $data['info'] = $goodsInfo;
    $filter['goodsNum']= $setting['goodsNum'];
    $data['goodsdata'] = b2c_widgets::load('Goods')->getGoodsList($filter);
    $o_pruchase = app::get('groupactivity')->model('purchase');
    $pruchase_arr = $o_pruchase->getList('act_id,gid,start_value,buy,price,state,start_time,end_time,act_open',array('gid|in'=>$goodsId));
    foreach($pruchase_arr as $k=>$v){
        if($v['act_open']=='false'){
            unset($data['goodsdata']['goodsRows'][$v['gid']]);
        }else{
            if($data['info'][$v['gid']]){
                $data['goodsdata']['goodsRows'][$v['gid']]['nice']=$data['info'][$v['gid']]['nice'];
                $data['goodsdata']['goodsRows'][$v['gid']]['pic']=$data['info'][$v['gid']]['pic'];
            }
            $data['goodsdata']['goodsRows'][$v['gid']]['act_id'] = $v['act_id'];
            $data['goodsdata']['goodsRows'][$v['gid']]['quantity'] = (int)$v['start_value']+(int)$v['buy'];
            $data['goodsdata']['goodsRows'][$v['gid']]['groupprice'] = $v['price'];
            $data['goodsdata']['goodsRows'][$v['gid']]['state'] = $v['state'];
            $data['goodsdata']['goodsRows'][$v['gid']]['start_time'] = $v['start_time'];
            $data['goodsdata']['goodsRows'][$v['gid']]['end_time'] = $v['end_time'];
            $data['goodsdata']['goodsRows'][$v['gid']]['sales'] = round($v['price']/$data['goodsdata']['goodsRows'][$v['gid']]['goodsSalePrice'],2)*10;
            $data['goodsdata']['goodsRows'][$v['gid']]['goodsLink'] = app::get('site')->router()->gen_url(array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'index','args'=>array($v['act_id'])));
            $data['request_widget_data'] = kernel::single('site_router')->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'request_widget_data') );
        }
    }
    foreach($data['goodsdata']['goodsRows'] as $ck=>$cv){
        if(!$cv['state']){
            unset($data['goodsdata']['goodsRows'][$ck]);
        }
    }
    return $data; 
}
?>
