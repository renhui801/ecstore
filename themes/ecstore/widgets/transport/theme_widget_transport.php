<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function theme_widget_transport(&$setting,&$smarty){
    
    //return ;//todo 修复挂件
    //$delivery =&$app->model('trading/delivery');
    $number=intval($setting['rowNum'])?intval($setting['rowNum']):5;
    $filter['status']="Y";
    $aTmp=array();
    //$result=$delivery ->getTopDelivery($number);
    
    $objDelivery = app::get('b2c')->model("delivery");
    $result = $objDelivery->getLatestDelivery($number);
    /*$sql ='SELECT * FROM sdb_b2c_delivery order by t_begin DESC';
    $result=$a->selectLimit($sql,$number,0);*/
    
    //$setting['smallPic'] = app::get('b2c')->res_url . '/icons/' . $setting['smallPic'];
    $i = 0;
    if ($result)
    {
        foreach($result as $key => $val)
        {
            $aTmp[$i]['transport'] = $val['logi_name']?$val['logi_name']:$val['delivery'];
            $aTmp[$i]['delivery_id'] = $val['delivery_id'];
            $aTmp[$i]['logi_no'] = $val['logi_no'];
            $aTmp[$i]['order_id'] = $val['order_id'];
            $aTmp[$i]['ship_name'] = $val['ship_name'];

            //$aTmp[$key]['status'] = $val['status'];
            //$aTmp[$key]['date'] = date("Y-m-d",$val['createtime']);
            //$aTmp[$key]['total_amount'] = $val['total_amount'];
            $i++;
        }
    }
    return $aTmp;
}
?>
