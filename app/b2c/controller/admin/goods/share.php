<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_goods_share extends desktop_controller{

    var $workground = 'b2c_ctl_admin_goods';

    function index(){
        $this->finder('b2c_mdl_goods_share',array(
            'title'=>app::get('b2c')->_('商品分享配置'),
            'use_buildin_recycle'=>false,
            ));
    }

    function setting(){
        $share_name = $_GET['name'];
        $setting = app::get('b2c')->model('goods_share')->getList('*',array('name'=>$share_name));
        $this->pagedata['setting'] = current($setting);
        $this->display('admin/goods/share.html');
    }

    function save(){
        $this->begin('index.php?ctl=admin_goods_share&act=index');
        $data[$_POST['name']] = $_POST;
        if(app::get('b2c')->model('goods_share')->save($data)){
            $this->end(true,app::get('b2c')->_('保存成功!'));
        }else{
            $this->end(false,app::get('b2c')->_('保存失败!'));
        }
    }

}
