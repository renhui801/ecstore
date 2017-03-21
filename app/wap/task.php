<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class wap_task
{
    function post_install()
    {

        kernel::single('base_initial', 'wap')->init();
        pam_account::register_account_type('wap','member',app::get('wap')->_('前台会员系统'));

        //更新wap.xml
        $rows = app::get('base')->model('apps')->getList('app_id',array('installed'=>1));
        foreach($rows as $r){
            if($r['app_id'] == 'base' || $r['app_id'] == 'wap')  continue;
            $args[] = $r['app_id'];
        }
        foreach ((array)$args as $app)
        {
            $this->xml_update($app);
        }

        //初始化模板
        logger::info('Initial themes');
        kernel::single('wap_theme_base')->set_last_modify();
        kernel::single('wap_theme_install')->initthemes();
        $themes = kernel::single('wap_theme_install')->check_install();

        //初始化logo
        $this->init_logo();

        // register wap_goods_description column wap_desc
        $this->register_wap_goods_intro();
    }//End Function

    function post_update( ){
        if($dbver['dbver'] < 2.1){ 
            kernel::single('base_initial', 'wap')->init();
        }
        pam_account::register_account_type('wap','member',app::get('wap')->_('前台会员系统'));
    }

    /**
    * xml文件的更新操作
    * @param object $app app对象实例
    */
    private function xml_update($app)
    {
        if (!$app) return;

        $detector = kernel::single('wap_application_module');
        foreach($detector->detect($app) as $name=>$item){
            $item->install();
        }
    }

    //初始化logo
    private function init_logo(){
        logger::info('Initial wap logo');
        $app_dir = app::get('wap')->app_dir;
        $logo = app::get('wap')->getConf('wap.logo');
        $obj_image = app::get('image')->model('image');
        $logo = $logo ? $logo : $obj_image->gen_id();
        $image_id = $obj_image->store($app_dir.'/initial/images/logo.png',$logo);
        app::get('wap')->setConf('wap.logo',$image_id);

        $desktop = app::get('wap')->getConf('wap.apple.desktop');
        $desktop = $desktop ? $desktop : $obj_image->gen_id();
        $image_id = $obj_image->store($app_dir.'/initial/images/desktop.png',$desktop);
        app::get('wap')->setConf('wap.apple.desktop',$image_id);
    }

    //wap->description
    private function register_wap_goods_intro(){
        $objGoods = app::get('b2c')->model('goods');
        $column = array(
            'wapintro' => array (
                'type' => 'longtext',
                'label' => app::get('b2c')->_('wap详细介绍'),
                'width' => 110,
                'hidden' => true,
                'editable' => false,
                'filtertype' => 'normal',
                'in_list' => false,
            ),
        );
        return $objGoods->meta_register($column);
    }

    public function post_uninstall(){
        $objGoods = app::get('b2c')->model('goods');
        $objGoods->meta_meta('wapintro');

        app::get('wap')->setConf('wap.status',false);
    }

}//End Class
