<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class wap_finder_theme
{
    // public $addon_cols='theme,stime,author,site,version';

    public $column_preview='预览';
    public $column_preview_width='140';
    public function column_preview($row){
        $dir = WAP_THEME_DIR . '/' . $row[$this->col_prefix.'theme'];
        if(is_dir($dir)){
            $current_theme = kernel::single('wap_theme_base')->get_default();
            
            $current_sytle = kernel::single('wap_theme_base')->get_theme_style($row[$this->col_prefix.'theme']);

            $preview = ($current_sytle['preview']) ? $current_sytle['preview'] : 'preview.jpg';
            $_tm = $row[$this->col_prefix.'theme'];
            $_active = ($_tm == $current_theme);
            
            
            if($_active){
                $style_addon = "border:2px solid #6888C8; border-bottom:none";
                $style_addon2 = "border-color:#6888C8;";
            }
            $theme_url = defined('THEMES_IMG_URL') ? THEMES_IMG_URL : kernel::base_url(1) . '/wap_themes';
            $_html =  sprintf('<div onmouseover="$(this).set(\'detail\',$(this).getParent(\'.row\').getElement(\'.btn-detail-open\').get(\'detail\'));" style="border:2px solid #E4EAF1; width: 120px; height: 140px;cursor:pointer;text-align:center;overflow:hidden; background:#fff;'.$style_addon.'"><img onload="$(this).zoomImg
            (120,136);" src="%s" id="%s" style="float:none"></div>', $theme_url. "/" . $row[$this->col_prefix.'theme'] . '/' . $preview . '?' . time(), $row[$this->col_prefix.'theme'].'_img');
            
            $_html.='<div style="width:120px;line-height:20px; background:#E4EAF1; border:2px solid #E4EAF1;'.$style_addon2.'">';
            
            if($_active){   
            $_html.='<div class="t-c" style="font-weight:bold; color:#fff; background:#6888C8; ">'.app::get('wap')->_('已启用').'</div>';
            }else{
            $_html.='<div class="t-c" style=""><a style="color:#000" href="index.php?app=wap&ctl=admin_theme_manage&act=set_default&theme='.$_tm.'&finder_id='.$_GET['_finder']['finder_id'].'">'.app::get('wap')->_('启用此模板').'</a></div>';  
            }

            $_html.='</div>';
                
                
            return $_html;
        }else{
            
            return '<div>'.app::get('wap')->_('模板目录已被移除').'</div>';
        }
    }

    public $detail_tmpl = '模板编辑';
    public function detail_tmpl($id){
        $data = app::get('wap')->model('themes')->getList('*', array('theme'=>$id));
        $render = app::get('wap')->render();
        $theme = $data[0]['theme'];
        $render->pagedata['list'] = kernel::single('wap_theme_tmpl')->get_edit_list($theme);
        $render->pagedata['types'] = kernel::single('wap_theme_tmpl')->get_name();
        $render->pagedata['theme'] = $theme;
        return $render->fetch('admin/theme/tmpl/frame.html');
    }

    public $detail_info = '基本信息';
    public function detail_info($id) 
    {
        $data = app::get('wap')->model('themes')->getList('*', array('theme'=>$id));
        $render = app::get('wap')->render();
        $row = $data[0];
        $row['config'] = $row['config'];
        $render->pagedata['theme'] = $id;
        $render->pagedata['row'] = $row;
        $widgets = app::get('wap')->model('widgets')->select()->where('theme = ?', $row['theme'])->instance()->fetch_all();
        foreach($widgets AS $k=>$v){
            $widgets[$k]['info'] = kernel::single('wap_theme_widget')->widgets_info($v['name'], $v['app'], $v['theme']);
        }
        $render->pagedata['widgets'] = $widgets;
        
        
        $option = '';
        if(file_exists(WAP_THEME_DIR . '/' . $id . '/theme.xml')) {
            $option .= '<option value="theme.xml">'.app::get('wap')->_('默认').'</option>';
        }
        if(file_exists(WAP_THEME_DIR . '/' . $id . '/theme_bak.xml')) {
            $option .= '<option value="theme_bak.xml">'.app::get('wap')->_('最近一次备份').'</option>';
        }
        $render->pagedata['resetoption'] = $option;

        return $render->fetch('admin/theme/detail/info.html');
    }//End Function


}//End Class
