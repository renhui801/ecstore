<?php
/**
 * cps_init
 * CPS数据库初始功能类
 * 
 * @uses
 * @package
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_init Jul 15, 2011  5:21:57 PM ever $
 */
class cps_init {
    
//     public function initTask() {
//         $mdlThemes = app::get('site')->model('themes');
//         $themes = $mdlThemes->getList('theme');
//         foreach ($themes as $theme) {
//             $this->saveTmpl($theme['theme']);
//             $this->saveWidget($theme['theme']);
//         }
//     }

    public function rmTask() {
        $mdlThemes = app::get('site')->model('themes');
        $themes = $mdlThemes->getList('theme');
        foreach ($themes as $theme) {
            $this->delTmpl($theme['theme']);
            $this->delWidget($theme['theme']);
        }
    }

    /**
     * 保存模板
     */
    public function saveTmpl($theme) {
        $mdlTmpl = app::get('site')->model('themes_tmpl');
        $arrTmpl = $this->getTmpl($theme);
        $libTmpl = kernel::single('site_theme_tmpl');

        $mdlThemes = app::get('site')->model('themes');
        $curTheme = $mdlThemes->dump(array('is_used' => 'true'), 'theme');

        $obj_themes_file = app::get('site')->model('themes_file');
        foreach ((array)$arrTmpl as $row) {
            //获取themes_file表中此文件的id
            $file_rows = $obj_themes_file->getList('id',array('theme'=>$row['theme'],'fileuri'=>$row['theme'] . ':' . $row['tmpl_path']));
            $row['rel_file_id'] = $file_rows['0']['id'];
            $mdlTmpl->save($row);
            $libTmpl->set_default($row['tmpl_type'], $theme, $row['tmpl_path']);
        }
    }

    /**
     * 保存挂件
     */
    public function saveWidget($theme) {
        $mdlWidget = app::get('site')->model('widgets_instance');
        $arrWidget = $this->getWidgets($theme);
        foreach ((array)$arrWidget as $row) {
            $mdlWidget->save($row);
        }
    }

    /**
     * 删除模板
     */
    public function delTmpl($theme) {
        $mdlTmpl = app::get('site')->model('themes_tmpl');
        $arrTmpl = $this->getTmpl($theme);
        $libTmpl = kernel::single('site_theme_tmpl');
        foreach ((array)$arrTmpl as $row) {
            $mdlTmpl->delete(array('tmpl_type' => $row['tmpl_type']));
            $libTmpl->del_default($row['tmpl_type'], $theme);
        }
    }

    /**
     * 删除挂件
     */
    public function delWidget($theme) {
        $mdlWidget = app::get('site')->model('widgets_instance');
        $arrWidget = $this->getWidgets($theme);
        foreach ((array)$arrWidget as $row) {
            $mdlWidget->delete(array('core_file' => $row['core_file']));
        }
    }

    /**
     * 获取模板
     * @return array
     */
    public function getTmpl($theme) {
        $themeTmpl = array(
            array('tmpl_type'=>'cps_index', 'tmpl_name'=>'网站联盟首页','tmpl_path'=>'cps_index.html', 'theme'=>$theme),
            array('tmpl_type'=>'cps_common','tmpl_name'=>'网站联盟协议','tmpl_path'=>'cps_common.html','theme'=>$theme),
            array('tmpl_type'=>'cps_notice','tmpl_name'=>'网站联盟公告','tmpl_path'=>'cps_notice.html','theme'=>$theme),
        );

        return $themeTmpl;
    }

    /**
     * 获取挂件
     * @return array
     */
/*    private function getWidgets($theme) {
        $picUrl = kernel::base_url(1).'/themes/' . $theme . '/images/cps';
        $siteWidgets = array(
            array('core_file'=>$theme . '/cps/header.html','core_slot'=>'1','core_id'=>'cps_menu','widgets_type'=>'menu','app'=>'cps','theme'=>'','widgets_order'=>'1','title'=>'网站联盟导航菜单','domid'=>'1309505301201','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html','core_slot'=>'1','core_id'=>'cps_artlist','widgets_type'=>'notice','app'=>'cps','theme'=>'','widgets_order'=>'3','title'=>'网站联盟公告','domid'=>'1309746485428','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('limit' => '7'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html','core_slot'=>'4','core_id'=>'cps_flow','widgets_type'=>'flow','app'=>'cps','theme'=>'','widgets_order'=>'7','title'=>'网站联盟流程','domid'=>'1309746695719','border'=>'borders/1pxGrayBorder.html','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html','core_slot'=>'5','core_id'=>'lm_artlist','widgets_type'=>'faq','app'=>'cps','theme'=>'','widgets_order'=>'8','title'=>'常见问题','domid'=>'1309746548424','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('limit' => '4'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html','core_slot'=>'6','core_id'=>'cps_blog_coop','widgets_type'=>'blog_coop','app'=>'cps','theme'=>'','widgets_order'=>'9','title'=>'网站联盟博客合作','domid'=>'1309746588263','border'=>'borders/1pxGrayBorder.html','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html','core_slot'=>'0','core_id'=>'cps_login','widgets_type'=>'user','app'=>'cps','theme'=>'','widgets_order'=>'2','title'=>'联盟会员注册/登录','domid'=>'1309942845325','border'=>'borders/1pxGrayBorder.html','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_notice.html','core_slot'=>'0','core_id'=>'cps_info_list','widgets_type'=>'notice','app'=>'cps','theme'=>'','widgets_order'=>'2','title'=>'网站联盟公告','domid'=>'1310104626012','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('limit' => '7'),'modified'=>'1310527209'),
            array('core_file'=>$theme . '/cps/header.html','core_slot'=>'0','core_id'=>'cps_contact','widgets_type'=>'contact','app'=>'cps','theme'=>'','widgets_order'=>'0','title'=>'网站联盟联系方式','domid'=>'1310526818382','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('tel' => '400-890-8858', 'email' => 'CustomerService@shopex.cn'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html','core_slot'=>'2','core_id'=>'cps_img','widgets_type'=>'ad_pic','app'=>'b2c','theme'=>'','widgets_order'=>'4','title'=>'','domid'=>'1310558248044','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('ad_pic_width'=>'','ad_pic_height'=>'','ad_pic'=>$picUrl . '/cps_65.jpg','ad_pic_link'=>$picUrl . '/cps_65.jpg'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html','core_slot'=>'3','core_id'=>'cps_flash_ad','widgets_type'=>'flashview','app'=>'b2c','theme'=>'','widgets_order'=>'5','title'=>'Flash广告','domid'=>'1310914397686','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('width'=>'710','height'=>'225','color'=>'default','duration'=>'2','flash'=>array(0=>array('i'=>'0','pic'=>$picUrl . '/cps_40.jpg','url'=>$picUrl . '/cps_40.jpg'),'1310914366063'=>array('i'=>'1310914366063','pic'=>$picUrl . '/lmpic.jpg','url'=>$picUrl . '/lmpic.jpg'))),'modified'=>'1310914503'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'0','core_id'=>'footnav1','widgets_type'=>'usercustom','app'=>'b2c','theme'=>'','widgets_order'=>'10','title'=>'关于我们','domid'=>'1310700381139','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/about/index.html" type="url" title="关于我们">关于我们</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'1','core_id'=>'footnav2','widgets_type'=>'usercustom','app'=>'b2c','theme'=>'','widgets_order'=>'11','title'=>'联系我们','domid'=>'1310700396189','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/help/contact.html" type="url" title="联系我们">联系我们</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'2','core_id'=>'footnav3','widgets_type'=>'usercustom','app'=>'b2c','theme'=>'','widgets_order'=>'12','title'=>'广告服务','domid'=>'1310700407675','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/service/index.html" type="url" title="广告服务">广告服务</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'3','core_id'=>'footnav4','widgets_type'=>'usercustom','app'=>'b2c','theme'=>'','widgets_order'=>'13','title'=>'人才招聘','domid'=>'1310700420146','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/partisan/index.html" type="url" title="人才招聘">人才招聘</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'4','core_id'=>'footnav5','widgets_type'=>'usercustom','app'=>'b2c','theme'=>'','widgets_order'=>'14','title'=>'友情链接','domid'=>'1310700437792','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/help/sitemap.html" type="url" title="友情链接">友情链接</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'5','core_id'=>'footnav6','widgets_type'=>'usercustom','app'=>'b2c','theme'=>'','widgets_order'=>'15','title'=>'常见问题','domid'=>'1310700452447','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/about/knowledge.html" type="url" title="常见问题">常见问题</a>'),'modified'=>'1310729881'),
        );

        return $siteWidgets;
    }*/
        private function getWidgets($theme) {
        $picUrl = kernel::base_url(1).'/themes/' . $theme . '/images/cps';
        $siteWidgets = array(
            array('core_file'=>$theme . '/cps/header.html','core_slot'=>'1','core_id'=>'cps_menu',     'widgets_type'=>'menu',       'app'=>'cps','theme'=>'','widgets_order'=>'1','title'=>'网站联盟导航菜单','domid'=>'1309505301201','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html', 'core_slot'=>'1','core_id'=>'cps_artlist',  'widgets_type'=>'notice',    'app'=>'cps','theme'=>'','widgets_order'=>'3','title'=>'网站联盟公告','domid'=>'1309746485428','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('limit' => '7'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html', 'core_slot'=>'4','core_id'=>'cps_flow',     'widgets_type'=>'flow',       'app'=>'cps','theme'=>'','widgets_order'=>'7','title'=>'网站联盟流程','domid'=>'1309746695719','border'=>'borders/1pxGrayBorder.html','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html', 'core_slot'=>'5','core_id'=>'lm_artlist',   'widgets_type'=>'faq',        'app'=>'cps','theme'=>'','widgets_order'=>'8','title'=>'常见问题','domid'=>'1309746548424','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('limit' => '4'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html', 'core_slot'=>'6','core_id'=>'cps_blog_coop','widgets_type'=>'blog_coop', 'app'=>'cps','theme'=>'','widgets_order'=>'9','title'=>'网站联盟博客合作','domid'=>'1309746588263','border'=>'borders/1pxGrayBorder.html','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html', 'core_slot'=>'0','core_id'=>'cps_login',     'widgets_type'=>'user',      'app'=>'cps','theme'=>'','widgets_order'=>'2','title'=>'联盟会员注册/登录','domid'=>'1309942845325','border'=>'borders/1pxGrayBorder.html','classname'=>'','tpl'=>'default.html','params'=>array(),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_notice.html','core_slot'=>'0','core_id'=>'cps_info_list','widgets_type'=>'notice',    'app'=>'cps','theme'=>'','widgets_order'=>'2','title'=>'网站联盟公告','domid'=>'1310104626012','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('limit' => '7'),'modified'=>'1310527209'),
            array('core_file'=>$theme . '/cps/header.html','core_slot'=>'0','core_id'=>'cps_contact',  'widgets_type'=>'contact',   'app'=>'cps','theme'=>'','widgets_order'=>'0','title'=>'网站联盟联系方式','domid'=>'1310526818382','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('tel' => '400-890-8858', 'email' => 'CustomerService@shopex.cn'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html', 'core_slot'=>'2','core_id'=>'cps_img',       'widgets_type'=>'ad_pic',    'app'=>'cps','theme'=>'','widgets_order'=>'4','title'=>'','domid'=>'1310558248044','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('ad_pic_width'=>'','ad_pic_height'=>'','ad_pic'=>$picUrl . '/cps_65.jpg','ad_pic_link'=>$picUrl . '/cps_65.jpg'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps_index.html', 'core_slot'=>'3','core_id'=>'cps_flash_ad', 'widgets_type'=>'flashview', 'app'=>'cps','theme'=>'','widgets_order'=>'5','title'=>'Flash广告','domid'=>'1310914397686','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('width'=>'710','height'=>'225','color'=>'default','duration'=>'2','flash'=>array(0=>array('i'=>'0','pic'=>$picUrl . '/cps_40.jpg','url'=>$picUrl . '/cps_40.jpg'),'1310914366063'=>array('i'=>'1310914366063','pic'=>$picUrl . '/lmpic.jpg','url'=>$picUrl . '/lmpic.jpg'))),'modified'=>'1310914503'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'0','core_id'=>'footnav1',     'widgets_type'=>'usercustom','app'=>'cps','theme'=>'','widgets_order'=>'10','title'=>'关于我们','domid'=>'1310700381139','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/about/index.html" type="url" title="关于我们">关于我们</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'1','core_id'=>'footnav2',     'widgets_type'=>'usercustom','app'=>'cps','theme'=>'','widgets_order'=>'11','title'=>'联系我们','domid'=>'1310700396189','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/help/contact.html" type="url" title="联系我们">联系我们</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'2','core_id'=>'footnav3',     'widgets_type'=>'usercustom','app'=>'cps','theme'=>'','widgets_order'=>'12','title'=>'广告服务','domid'=>'1310700407675','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/service/index.html" type="url" title="广告服务">广告服务</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'3','core_id'=>'footnav4',     'widgets_type'=>'usercustom','app'=>'cps','theme'=>'','widgets_order'=>'13','title'=>'人才招聘','domid'=>'1310700420146','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/partisan/index.html" type="url" title="人才招聘">人才招聘</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'4','core_id'=>'footnav5',     'widgets_type'=>'usercustom','app'=>'cps','theme'=>'','widgets_order'=>'14','title'=>'友情链接','domid'=>'1310700437792','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/help/sitemap.html" type="url" title="友情链接">友情链接</a>'),'modified'=>'1310729881'),
            array('core_file'=>$theme . '/cps/footer.html','core_slot'=>'5','core_id'=>'footnav6',     'widgets_type'=>'usercustom','app'=>'cps','theme'=>'','widgets_order'=>'15','title'=>'常见问题','domid'=>'1310700452447','border'=>'__none__','classname'=>'','tpl'=>'default.html','params'=>array('usercustom'=>'<a href="http://www.shopexecp.cn/about/knowledge.html" type="url" title="常见问题">常见问题</a>'),'modified'=>'1310729881'),
        );

        return $siteWidgets;
    }

}