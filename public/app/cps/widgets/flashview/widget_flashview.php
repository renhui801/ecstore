<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
function widget_flashview(&$setting,&$app){
    $setting['allimg']="";
    $setting['allurl']="";
    if(!$setting['flash']){
       foreach($setting['img'] as $value){
            $setting['allimg'].=$rvalue."|";
            $setting['allurl'].=urlencode($value["url"])."|";
       }
    }else{
        foreach($setting['flash'] as $key=>$value){
            if($value['pic']){
                if($value["url"]){
                    $value["link"]=$value["url"];
                }
                $setting['allimg'].=$rvalue."|";
                $setting['allurl'].=urlencode($value["link"])."|";
            }
        }
        krsort($setting['flash']);
    }
    return $setting;
}
?>
