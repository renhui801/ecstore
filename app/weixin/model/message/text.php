<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class weixin_mdl_message_text extends dbeav_model
{

    /**
     * 删除公众账号前判断
     */
    public function pre_recycle($rows){

        foreach( $rows as $row ){
            if( app::get('weixin')->model('message')->count(array('message_id'=>$row['id'],'message_type'=>'text')) ){
                $this->recycle_msg = app::get('weixin')->_('该文字消息已被微信消息互动绑定，不能删除');
                return false;
            }

            if( app::get('weixin')->model('menus')->count(array('msg_text'=>$row['id']))){
                $this->recycle_msg = app::get('weixin')->_('该文字消息已被自定义菜单绑定，不能删除');
                return false;
            }
        }
        return true;
    }

}
