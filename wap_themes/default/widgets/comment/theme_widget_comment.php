<?php
/**
* Power By ShopEx Jxwinter
* Time  2012-04-10  NO.193

*/

function theme_widget_comment($setting,&$smarty){

    $data = b2c_widgets::load('Comment')->getTopComment($setting['limit'],'wap');    //通过数据接口取数据

    foreach($data as $k=>$v){
      if(!$v['goodsPic']){
       $imageDefault = app::get('image')->getConf('image.set');
       $data[$k]['goodsPic'] = $imageDefault['S']['default_image'];
      }
    }

    return $data;
}
?>
