<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class cps_messenger_contact{
    
    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * 获取当前消息发送的类型信息
     * @access public
     * @param int $member_id 联盟商id
     * @param string $target 消息发送的依据信息email号，手机号或联盟商id
     * @param string $tmpl_name 调用的模板路径名称
     * @param string $sdfpath 消息调用的信息结构sdfpth
     * @return null
     * @version 1 Jun 28, 2011 创建
     */
    public function get_contact($member_id,&$target,$tmpl_name,$sdfpath){
        $aTp = explode('/',$tmpl_name);

        $actions = kernel::single('cps_service_firevent_action')->get_type();
        foreach($actions as $key =>$val){
            if($key == $aTp[1]){
                $obj_member = $this->app->model('users');
                $sdf = $obj_member->dump($member_id);
                eval(' $target= $sdf["'.implode('"]["',explode('/',$sdfpath)).'"]; ');
                break;
            }
        }
    }

}