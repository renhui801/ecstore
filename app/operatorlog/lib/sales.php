<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

#营销
class operatorlog_sales{

    function __construct(){
        $this->objlog = kernel::single('operatorlog_service_desktop_controller');
        $this->delimiter = kernel::single('operatorlog_service_desktop_controller')->get_delimiter();
    }


    function proregister_log($newdata,$olddata){
        $modify_flag = 0;
        $data = array();
        foreach($newdata as $key=>$val){
            if($newdata[$key] != $olddata[$key]){
                $data['new'][$key] = $val;
                $data['old'][$key] = $olddata[$key];
                $modify_flag++;
            }
        }
        if($modify_flag>0){
            $memo  = "serialize".$this->delimiter."编辑注册营销配置".$this->delimiter.serialize($data);
            $this->objlog->logs('编辑注册营销配置', '编辑注册营销配置', $memo);
        }
    }


    public function giftsave($giftname,$is_edit){
        if($is_edit){
            $this->objlog->logs('member', '编辑赠品', '编辑赠品 '.$giftname);
        }else{
            $this->objlog->logs('member', '添加赠品', '添加赠品 '.$giftname);
        }
    }


}//End Class
