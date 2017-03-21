<?php

/*function ecos_cactus_site_theme_widget_save_all($widgets_id,$widgets,$match,&$return,&$slots)
{
    if(!$widgets_id){
        return false;
    }else{
        $return[$_SESSION['_tmp_wg_insert'][$match[1]]['_domid']] = $widgets_id;
        unset($_SESSION['_tmp_wg_insert'][$match[1]]);
        $count = count($slots[$widgets['core_file']]);
        $slots[$widgets['core_file']][$count]=$widgets_id;

        return true;
    }
}*/

function ecos_cactus_site_theme_widget_widgets_config_empty($name,$data,$app)
{
    $data['crun'] = 'widget_cfg_' . $name;
    $data['cfg'] = $data['dir'] . '/widget_cfg_' . $name . '.php';
    $data['run'] = 'widget_' . $name;
    $data['func'] = $data['dir'] . '/' . $data['run'] . '.php';
    $data['flag'] = 'app_' . $app;
    return $data;
}



function ecos_cactus_site_theme_widget_widgets_config_theme($name,$data,$theme)
{
    $data['crun'] = 'theme_widget_cfg_' . $name;
    $data['cfg'] = $data['dir'] . '/theme_widget_cfg_' . $name . '.php';
    $data['run'] = 'theme_widget_' . $name;
    $data['func'] = $data['dir'] . '/' . $data['run'] . '.php';
    $data['flag'] = 'theme_' . $theme;
    return $data;
}

function ecos_cactus_site_theme_widget_widgets_get_libs_notype($info,$val,$widgetsLib=array())
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

function ecos_cactus_site_theme_widget_widgets_get_libs_type($info,$type,$val,$widgetsLib=array())
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

function ecos_cactus_site_theme_widget_prefix_content($content, $widgets_dir)
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

function ecos_cactus_site_theme_get_view($theme){
    if ($handle=opendir(THEME_DIR.'/'.$theme)){
        $views = array();
        while(false!==($file=readdir($handle))){
            if ($file{0}!=='.' && $file{0}!=='_' && is_file(THEME_DIR.'/'.$theme.'/'.$file) && (($t=strtolower(strstr($file,'.')))=='.html' || $t=='.htm')){
                $views[] = $file;
            }
        }
        closedir($handle);
        return $views;
    }else{
        return false;
    }
}

function ecos_cactus_site_theme_get_source_code($theme,$tmpl_type){
    $file = THEME_DIR . '/' . $theme . '/' . $tmpl_type.'.html';
    if(!is_file($file)){
        $file = THEME_DIR . '/' . $theme . '/default.html';
    }

    if(is_file($file)){
        $content = file_get_contents($file);
    }else{
        $content = '<{main}>';
    }
    return $content;
            /** 默认第一次为首页 **/
/*         if (!$tmpl_type){
            $default_file = THEME_DIR . '/' . $theme . '/index.html';
        }else{
            $default_file = THEME_DIR . '/' . $theme . '/' . $tmpl_type.'.html';
        }

        if(is_file($default_file)){
            $content = file_get_contents($default_file);
        }else{
            $content = '<{require file="block/header.html"}>
                        <div class="AllWrapInside clearfix">
                          <div class="mainColumn pageMain"><{widgets id="nav"}>  <{main}> </div>
                          <div class="sideColumn pageSide"> <{widgets id="sideritems"}> </div>
                        </div>
                        <{require file="block/footer.html"}>';
        } */
}



function ecos_cactus_site_theme_get_theme_dir($theme, $open_path){
    if(ECAE_MODE==true){
        return THEME_DIR . '/' . $theme . str_replace(array('-','.'), array('/','/'), $open_path);
    }else{
        return realpath(THEME_DIR . '/' . $theme . '/' . str_replace(array('-','.'), array('/','/'), $open_path));
    }
}

function ecos_cactus_site_check_demosite($html){
    if(defined('DEV_CHECKDEMO') && DEV_CHECKDEMO){
        $pattern = "/<title>(.*)<\/title>/";
        preg_match($pattern,$html,$title);
        $newtitle = "<title>测试环境，请勿进行真实业务行为_".$title[1]."</title>";
        $html = preg_replace($pattern,$newtitle,$html);
    }
    return $html;
}


function ecos_cactus_site_copyr($html){
    return base64_decode('PGRpdiBzdHlsZT0iY29sb3I6IzMzMztmb250LWZhbWlseTpWZXJkYW5hO2ZvbnQtc2l6ZToxMXB4O2xpbmUtaGVpZ2h0OjIwcHghaW1wb3J0YW50O292ZXJmbG93OnZpc2libGUhaW1wb3J0YW50O2Rpc3BsYXk6YmxvY2shaW1wb3J0YW50O3Zpc2liaWxpdHk6dmlzaWJsZSFpbXBvcnRhbnQ7cG9zaXRpb246cmVsYXRpdmU7ei1JbmRleDo2NTUzNSFpbXBvcnRhbnQ7dGV4dC1hbGlnbjpjZW50ZXI7Ij4KUG93ZXJlZCBCeSA8YSBzdHlsZT0idGV4dC1kZWNvcmF0aW9uOm5vbmUiIGhyZWY9Imh0dHA6Ly93d3cuc2hvcGV4LmNuIiB0YXJnZXQ9Il9ibGFuayI+PGIgc3R5bGU9ImNvbG9yOiByZ2IoOTIsIDExMywgMTU4KTsiPlNob3A8L2I+PGIgc3R5bGU9ImNvbG9yOiByZ2IoMjQzLCAxNDQsIDApOyI+RXg8L2I+PC9hPiAKPC9kaXY+');
}