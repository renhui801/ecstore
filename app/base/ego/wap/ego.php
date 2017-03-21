<?php

function ecos_cactus_wap_theme_widget_widgets_config_theme($name,$data,$theme)
{
    $data['crun'] = 'theme_widget_cfg_' . $name;
    $data['cfg'] = $data['dir'] . '/theme_widget_cfg_' . $name . '.php';
    $data['run'] = 'theme_widget_' . $name;
    $data['func'] = $data['dir'] . '/' . $data['run'] . '.php';
    $data['flag'] = 'theme_' . $theme;
    return $data;
}

function ecos_cactus_wap_theme_widget_widgets_get_libs_notype($info,$val,$widgetsLib=array())
{
    if($info['catalog']){
        if(!$widgetsLib['list'][$info['catalog']]){
            $widgetsLib['list'][$info['catalog']]=$info['catalog'];
        }
    }
    if($info['usual']=='1'){
        $count = count($widgetsLib['usual']);
        $widgetsLib['usual'][$count]=array('sort'=>$info['order'],'description'=>$info['description'],'name'=>$val['name'], 'app'=>$val['app'],'theme'=>$val['theme'],'label'=>$info['name'],'folder'=>$info['type']);
    }
    return $widgetsLib;
}

function ecos_cactus_wap_theme_widget_widgets_get_libs_type($info,$type,$val,$widgetsLib=array())
{
    if($info['catalog']==$type){
        $order[count($order)]=$info['order']?$info['order']:0;
        $count = count($widgetsLib['list']);
        $widgetsLib['list'][$count] = array('sort'=>$info['order'],'description'=>$info['description'],'name'=>$val['name'], 'app'=>$val['app'],'theme'=>$val['theme'],'label'=>$info['name'],'folder'=>$info['type']);
    }
    return $widgetsLib;
    /*
    if($info['usual']=='1'){
        $widgetsLib['usual'][]=array('sort'=>$info['order'],'description'=>$info['description'],'name'=>$file,'label'=>$info['name']);
    }
    */
}

function ecos_cactus_wap_theme_widget_prefix_content($content, $widgets_dir)
{
    $pattern = array(
        '/(\'|\")(images\/)/is',
        '/((?:background|src|href)\s*=\s*["|\'])(?:\.\/|\.\.\/)?(images\/.*?["|\'])/is',
        '/((?:background|background-image):\s*?url\()(?:\.\/|\.\.\/)?(images\/)/is',
    );
    $replacement = array(
        "\$1" . $widgets_dir .'/' . "\$2",
        "\$1" . $widgets_dir .'/' . "\$2",
        "\$1" . $widgets_dir .'/' . "\$2",
    );
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}

function ecos_cactus_wap_wapcopyr($html){
    return base64_decode('PGRpdiBzdHlsZT0iY29sb3I6IzMzMztmb250LWZhbWlseTpWZXJkYW5hO2ZvbnQtc2l6ZToxMXB4O2xpbmUtaGVpZ2h0OjIwcHghaW1wb3J0YW50O292ZXJmbG93OnZpc2libGUhaW1wb3J0YW50O2Rpc3BsYXk6YmxvY2shaW1wb3J0YW50O3Zpc2liaWxpdHk6dmlzaWJsZSFpbXBvcnRhbnQ7cG9zaXRpb246cmVsYXRpdmU7ei1JbmRleDo2NTUzNSFpbXBvcnRhbnQ7dGV4dC1hbGlnbjpjZW50ZXI7Ij4KUG93ZXJlZCBCeSA8YSBzdHlsZT0idGV4dC1kZWNvcmF0aW9uOm5vbmUiIGhyZWY9Imh0dHA6Ly93d3cuc2hvcGV4LmNuIiB0YXJnZXQ9Il9ibGFuayI+PGIgc3R5bGU9ImNvbG9yOiByZ2IoOTIsIDExMywgMTU4KTsiPlNob3A8L2I+PGIgc3R5bGU9ImNvbG9yOiByZ2IoMjQzLCAxNDQsIDApOyI+RXg8L2I+PC9hPiAKPC9kaXY+');
}