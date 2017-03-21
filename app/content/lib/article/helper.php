<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
* 加载头部尾部类
*/
class content_article_helper
{

    /**
    * 头部
    */
    function function_header(){
        $ret='<base href="'.kernel::base_url(1).'"/>';
        $path = app::get('site')->res_url;

        $debug_css = defined('DEBUG_CSS') && constant('DEBUG_CSS');
        $debug_js = defined('DEBUG_JS') && constant('DEBUG_JS');
        $css_mini = $debug_css ? '' : '_mini';
        $cssver = kernel::single('base_component_ui')->getVer($debug_css);
        $jsver = kernel::single('base_component_ui')->getVer($debug_js);
        if(!defined("DONOTUSE_CSSFRAMEWORK") || !constant('DONOTUSE_CSSFRAMEWORK')) {
            $ret.= '<link rel="stylesheet" href="'.$path.'/css'.$css_mini.'/typical.css'.$cssver.'" />';
        }
        $ret.='<link rel="stylesheet" href="'.$path.'/css'.$css_mini.'/widgets_edit.css'.$cssver.'" />';
        $ret.= kernel::single('base_component_ui')->lang_script(array('src'=>'lang.js', 'app'=>'site', 'pdir'=>'js_mini'));
        $ret.= kernel::single('base_component_ui')->lang_script(array('src'=>'lang.js', 'app'=>'b2c', 'pdir'=>'js_mini'));
        if($debug_js){
            $ret.= '<script src="'.$path.'/js/mootools.js?'.$jsver.'"></script>
            <script src="'.$path.'/js/moomore.js'.$jsver.'"></script>
            <script src="'.$path.'/js/jstools.js'.$jsver.'"></script>
            <script src="'.$path.'/js/switchable.js'.$jsver.'"></script>
            <script src="'.$path.'/js/dragdropplus.js'.$jsver.'"></script>
            <script src="'.$path.'/js/shopwidgets.js'.$jsver.'"></script>';
        }else{
            $ret.= '<script src="'.$path.'/js_mini/moo.min.js'.$jsver.'"></script>
            <script src="'.$path.'/js_mini/ui.min.js'.$jsver.'"></script>
            <script src="'.$path.'/js_mini/shopwidgets.min.js'.$jsver.'"></script>';
        }
        foreach(kernel::serviceList('site_theme_view_helper') AS $service){
            if(method_exists($service, 'function_header')){
                $ret .= $service->function_header();
            }
        }
        return $ret;
    }

    /**
    * 尾部
    */
    function function_footer(){
       return '<div id="drag_operate_box" class="drag_operate_box" style="visibility:hidden;">
           <div class="drag_handle_box">
              <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td width="117" align="left" style="text-align:left;"><span class="add-widgets-wrap"><a class="btn-operate btn-edit-widgets" title="'.app::get('site')->_('编辑此挂件').'">'.app::get('site')->_('编辑').'</a><!--<a class="btn-operate btn-save-widgets">'.app::get('site')->_('另存为样例').'</a>--> <a class="btn-operate btn-add-widgets" id="btn_add_widget"><i class="icon"></i>'.app::get('site')->_('添加挂件').'</a><ul class="widget-drop-menu" id="add_widget_dropmenu"><li class="before" title="'.app::get('site')->_('添加到此挂件上方').'">'.app::get('site')->_('添加到上方').'</li><li class="after" title="'.app::get('site')->_('添加到此挂件下方').'">'.app::get('site')->_('添加到下方').'</li></ul></span></td>
                  <td class="operate-title" style="_width:85px;" align="center"><a class="btn-operate btn-up-slot" title="'.app::get('site')->_('上移').'">&#12288;</a> <a class="btn-operate btn-down-slot" title="'.app::get('site')->_('下移').'">&#12288;</a></td>
                  <td width="36" align="right" style="text-align:right;"><a class="btn-operate btn-del-widgets" title="'.app::get('site')->_('删除此挂件').'">'.app::get('site')->_('删除').'</a></td>
                </tr>
              </table>
            </div>
        </div>
        <div id="drag_ghost_box" class="drag_ghost_box" style="visibility:hidden"></div>
        <script>new top.DropMenu($("btn_add_widget"), {menu:$("add_widget_dropmenu"),eventType:"mouse",offset:{x:-1, y:0},temppos:true,relative:$$(".add-widgets-wrap")[0]});</script>';
    }

}//End Class
