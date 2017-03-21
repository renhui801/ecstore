<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class wap_theme_helper
{
    function function_wapheader(){
        $ret='<base href="'.kernel::base_url(1).'"/>';
        $path = app::get('wap')->res_full_url;

        $css_mini = (defined('DEBUG_CSS') && constant('DEBUG_CSS')) ? '' : '_mini';
        $ret.='<link rel="stylesheet" href="'.$path.'/css'.$css_mini.'/widgets_edit.css" type="text/css" />';
        $ret.='<link rel="stylesheet" href="'.$path.'/css'.$css_mini.'/styles.css" type="text/css" />';

        $ret.=kernel::single('base_component_ui')->lang_script(array('src'=>'lang.js', 'app'=>'site'));
        if(defined('DEBUG_JS') && constant('DEBUG_JS')){
            $ret.= '<script src="'.$path.'/js/mootools.js?'.time().'"></script>
                    <script src="'.$path.'/js/moomore.js?'.time().'"></script>
                    <script src="'.$path.'/js/jstools.js?'.time().'"></script>
                    <script src="'.$path.'/js/switchable.js?'.time().'"></script>
                    <script src="'.$path.'/js/dragdropplus.js?'.time().'"></script>
                    <script src="'.$path.'/js/shopwidgets.js?'.time().'"></script>';
        }else{
            $ret.= '<script src="'.$path.'/js_mini/moo.min.js"></script>
                <script src="'.$path.'/js_mini/ui.min.js"></script>
                <script src="'.$path.'/js_mini/shopwidgets.min.js"></script>';
        }
            //$ret.='<script src="'.$path.'/js_mini/patch.js"></script>';
        if($theme_info=(app::get('wap')->getConf('wap.theme_'.app::get('wap')->getConf('current_theme').'_color'))){
            $theme_color_href=kernel::base_url(1).'/wap_themes/'.app::get('wap')->getConf('current_theme').'/'.$theme_info;
            $ret.="<script>
            window.addEvent('domready',function(){
                new Element('link',{href:'".$theme_color_href."',type:'text/css',rel:'stylesheet'}).inject(document.head);
             });
            </script>";
        }

        return $ret;
    }

    function function_wapfooter(){
        return '<div id="drag_operate_box" class="drag_operate_box" style="visibility:hidden;">
        <div class="drag_handle_box">
          <table cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td width="117" align="left" style="text-align:left;"><span class="add-widgets-wrap"><a class="btn-operate btn-edit-widgets" title="'.app::get('wap')->_('编辑此挂件').'">'.app::get('wap')->_('编辑').'</a><!--<a class="btn-operate btn-save-widgets">'.app::get('wap')->_('另存为样例').'</a>--> <a class="btn-operate btn-add-widgets" id="btn_add_widget"><i class="icon"></i>'.app::get('wap')->_('添加挂件').'</a><ul class="widget-drop-menu" id="add_widget_dropmenu"><li class="before" title="'.app::get('wap')->_('添加到此挂件上方').'">'.app::get('wap')->_('添加到上方').'</li><li class="after" title="'.app::get('wap')->_('添加到此挂件下方').'">'.app::get('wap')->_('添加到下方').'</li></ul></span></td>
              <td class="operate-title" style="_width:85px;" align="center"><a class="btn-operate btn-up-slot" title="'.app::get('wap')->_('上移').'">&#12288;</a> <a class="btn-operate btn-down-slot" title="'.app::get('wap')->_('下移').'">&#12288;</a></td>
              <td width="36" align="right" style="text-align:right;"><a class="btn-operate btn-del-widgets" title="'.app::get('wap')->_('删除此挂件').'">'.app::get('wap')->_('删除').'</a></td>
            </tr>
          </table>
        </div>
        <div class="drag_rules" style="display:none;">
          <div class="drag_left_arrow">&larr;</div>
          <div class="drag_annotation"><em></em></div>
          <div class="drag_right_arrow">&rarr;</div>
        </div>
        <!--<div class="content"></div>-->
        </div>
        <div id="drag_ghost_box" class="drag_ghost_box" style="visibility:hidden"></div>
        <script>new top.DropMenu($("btn_add_widget"), {menu:$("add_widget_dropmenu"),eventType:"mouse",offset:{x:-1, y:0},temppos:true,relative:$$(".add-widgets-wrap")[0]});</script>';
    }


}//End Class
