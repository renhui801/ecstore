<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

/**
 * @package weixin
 * @subpackage dbeav_model
 * @copyright Copyright (c) 2010, shopex. inc
 * @author edwin.lzh@gmail.com
 * @license 
 */
class weixin_mdl_bind extends dbeav_model
{

    /**
     * 删除公众账号前判断
     */
    public function pre_recycle($rows){

        foreach( $rows as $row ){
            if( app::get('weixin')->model('menus')->count(array('bind_id'=>$row['id'])) ){
                $this->recycle_msg = app::get('weixin')->_('请先删除绑定在该账号中的自定义菜单,公众账号：').$row['name'];
            }

            if( app::get('weixin')->model('message')->count(array('bind_id'=>$row['id'])) ){
                $this->recycle_msg = app::get('weixin')->_('请先删除绑定在该账号中的自动回复消息,公众账号：').$row['name'];
                return false;
            }
        }
        return true;
    }

}
