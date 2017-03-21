<?php

class weixin_view_input{

    function input_htmllink($params){
        $id = 'mce_'.substr(md5(rand(0,time())),0,6);
        $includeBase=$params['includeBase']?$params['includeBase']:true;
        $params['id']=$id;

        $img_src = app::get('desktop')->res_url;
        $render = new base_render(app::get('weixin'));
        $render->pagedata['id'] = $id;
        $render->pagedata['img_src'] = $img_src;
        $render->pagedata['includeBase'] = $includeBase;
        if( !$params['height'] ) $params['height'] = '300px';
        if( !$params['title'] ) $params['title'] = app::get('weixin')->_('超级链接');
        if( !$params['link'] ) $params['link'] = 'index.php?app=weixin&ctl=admin_setting&act=link_view';
        $render->pagedata['params'] = $params;

        $style2=$render->fetch('editor/html_style2.html');
        $html=$style2;
        return $html;
    }


}
