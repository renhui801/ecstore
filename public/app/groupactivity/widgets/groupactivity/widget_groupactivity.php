<?php
function widget_groupactivity(&$setting,&$smarty){
    $goods_list = json_decode($setting['goods'],1);
    $goodsId = array();$goodsInfo = array();
    if (is_array($goods_list))
    foreach ($goods_list as $goods_k=>$goods){
        if($goods_k=='0'){
            $goodsId[] = $goods['id'];
            $img_id = app::get('b2c')->model('goods')->getList('image_default_id',array('goods_id'=>$goods['id']));
            $goods['pic'] = $img_id[0]['image_default_id'];
            $goodsInfo[$goods['id']] = $goods;
        }
    }
    $filter['goodsId'] = $goodsId;
    $data['info'] = $goodsInfo;
    $filter['goodsNum']= 1;
    $data['goodsdata'] = b2c_widgets::load('Goods')->getGoodsList($filter);
    $o_pruchase = app::get('groupactivity')->model('purchase');
    $pruchase_arr = $o_pruchase->getList('act_id,gid,start_value,buy,price,state,start_time,end_time,act_open',array('gid|in'=>$goodsId));
    foreach($pruchase_arr as $k=>$v){
        if($v['act_open']=='false'){
            unset($data['goodsdata']['goodsRows'][$v['gid']]);
        }else{
            $data['goodsdemo']=$data['goodsdata']['goodsRows'][$v['gid']];
            if($data['info'][$v['gid']]){
                $data['goodsdemo']['nice']=$data['info'][$v['gid']]['nice'];
                $data['goodsdemo']['pic']=$data['info'][$v['gid']]['pic'];
            }
            $data['goodsdemo']['gid'] = $v['gid'];
            $data['goodsdemo']['act_id'] = $v['act_id'];
            $data['goodsdemo']['quantity'] = (int)$v['start_value']+(int)$v['buy'];
            $data['goodsdemo']['groupprice'] = $v['price'];
            $data['goodsdemo']['state'] = $v['state'];
            $data['goodsdemo']['start_time'] = $v['start_time'];
            $data['goodsdemo']['end_time'] = $v['end_time'];
            $data['goodsdemo']['sales'] = round($v['price']/$data['goodsdata']['goodsRows'][$v['gid']]['goodsSalePrice'],2)*10;
            $data['goodsdemo']['goodsLink'] = app::get('site')->router()->gen_url(array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'index','args'=>array($v['act_id'])));
            $data['request_widget_data'] = kernel::single('site_router')->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'request_widget_data') );
            
        }
        break;
    }
    /*
    foreach($data['goodsdata']['goodsRows'] as $ck=>$cv){
        if(!$cv['state']){
            unset($data['goodsdata']['goodsRows'][$ck]);
        }
    }
    */
    unset($data['goodsdata']);
    return $data; 
}
?>
