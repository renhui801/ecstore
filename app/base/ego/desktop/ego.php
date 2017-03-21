<?php

/**
 *
 * finder id
 *
 * @param string $find_id
 * @return string
 */
function ecos_cactus_desktop_finder_find_id($find_id = '')
{
    if($find_id){
        return $find_id;
    }else{
        return substr(md5($_SERVER['QUERY_STRING']),5,6);
    }
}

/**
 *
 * 获取请求参数
 * @param array $params
 * @return array
 */
function ecos_cactus_desktop_finder_get_args($params = array())
{
    $extends = array();
    foreach( $params as $key => $val ) {
        if( $key!='app' && $key!='act' && $key!='ctl' && $key!='_finder' )
            $extends[$key] = $val;
        if( $key=='_finder' ) break;
    }
    return $extends;
}

/**
 *
 * 生成GET
 *
 * @param string $find_id
 * @return array
 */
function ecos_cactus_desktop_finder_make_get($find_id = '')
{
    $_GET['ctl'] = $_GET['ctl']?$_GET['ctl']:'default';
    $_GET['act'] = $_GET['act']?$_GET['act']:'index';
    $_GET['_finder']['finder_id'] = $find_id;
    if($_GET['action'])unset($_GET['action']);
    return $_GET;
}


/**
 *
 * 按model全名获取app名及model名
 * @param sting $model_name
 * @return array array(app_name , model_name);
 */
function ecos_cactus_desktop_finder_split_model($model_name)
{
    $return = array();
    if($p=strpos($model_name,'_mdl_')){
        $return[0] = substr($model_name,0,$p);
        $return[1] = substr($model_name,$p+5);
    }else{
        trigger_error('finder only accept full model name: '.$full_object_name, E_USER_ERROR);
    }
    return $return;
}


/**
 *
 * 获取列
 * @param string $cols
 * @param array $func_columns
 * @param array $default_in_list
 * @param array $all_cols
 * @return array
 */
function ecos_cactus_desktop_finder_get_columns($cols , $func_columns , $default_in_list , $all_cols)
{
    if($cols){
        return explode(',',$cols);
    }else{
        if($func_columns){
            foreach($func_columns as $key=>$func_column){
                $col_keys[count($col_keys)] = $key;
            }
        }
        $columns = array_merge((array)$col_keys,(array)$default_in_list);
        foreach($all_cols as $key=>$value){
            if(in_array($key,$columns)){
                $return[count($return)] = $key;
            }
        }
        return $return;
    }
}

/**
 *
 * 获取所有column
 * @param array $in_list
 * @param array $func_columns
 * @param array $dbschema_columns
 * @return array
 */
function ecos_cactus_desktop_finder_all_columns($in_list , $func_columns , $dbschema_columns)
{
    $columns = array();
    foreach((array)$in_list as $key){
        $columns[$key] = &$dbschema_columns[$key];
    }
    $return = array_merge((array)$func_columns,(array)$columns);
    foreach($return as $k=>$r){
        if(!$r['order']){
            $return[$k]['order'] = 100;

        }
        $orders[count($orders)] = $return[$k]['order'];
    }
    array_multisort($orders,SORT_ASC,$return);
    return $return;
}


function ecos_cactus_desktop_finder_builder_prototype_get_view_modifier($views, $finder_aliasname, $object_name, $views_temp=array())
{

    foreach((array)$views as $k=>$view){
        if(!isset($view['finder'])){
            // 缓存tab_view按钮下的数据数量
            $views_temp[$k] = $view;
            if($view['newcount']){
                cacheobject::set("view_tab_{$object_name}_{$k}",$view['addon'],300+time());
            }
            cacheobject::get("view_tab_{$object_name}_{$k}",$views_temp[$k]['addon']);
        }elseif(isset($view['finder'])){
            if(is_array($view['finder'])){
                if(in_array($finder_aliasname,$view['finder'])){
                    $views_temp[$k] = $view;
                }
            }elseif($finder_aliasname==$view['finder']){
                $views_temp[$k] = $view;
            }

        }
    }
    return $views_temp;
}

/**
 * 获取finder gen_url的array
 * @param array 扩展参数数组
 * @param array url的控制器数组
 * @return null
 */
function ecos_cactus_desktop_finder_builder_prototype_get_view_url_array($extends, $_url_array=array())
{
    if( $extends && is_array($extends) ) {
        foreach( $extends as $_key => $_val ) {
            if( array_key_exists($_key,$_url_array) ) continue;
            $_url_array[$_key] = $_val;
        }
    }
        return $_url_array;
}


function ecos_cactus_desktop_finder_builder_view_script_gen_finderoptions($__view,$__options,$finderOptions=array())
{
    //原来的代码
    //if($finderOptions['packet']) $finderOptions['packet'] = (count($this->__view)>0)?true:false;
    /** 判断是否要显示归类视图 **/
    // $is_display_packet = 'false';
    // if ($finderOptions['packet']){
    //     foreach ($this->__view as $arr){
    //         if ($arr['addon']){
    //             $is_display_packet = 'true';
    //             break;
    //         }
    //         else
    //             $is_display_packet = 'false';
    //     }
    // }
    // if ($is_display_packet == 'true')
    //     $finderOptions['packet'] = true;
    // else
    //     $finderOptions['packet'] = false;
    // /** end **/
    // if($this->options){
    //     $finderOptions = array_merge($finderOptions,$this->options);
    // }

    // //$arrow_down = $this->ui->img('bundle/arrow-down.gif',array('style'=>'margin-left:8px;'));

    // $finderOptions = json_encode($finderOptions);

    /** 判断是否要显示归类视图 **/
    $finderOptions['packet'] = $__view ? true : false;
    if($__options){
        $finderOptions = array_merge($finderOptions,$__options);
    }

    $finderOptions = json_encode($finderOptions);

    return $finderOptions;
}

function ecos_cactus_desktop_check_demosite($title){
    if(defined('DEV_CHECKDEMO') && DEV_CHECKDEMO){
        $title = "测试环境，请勿进行真实业务行为";
    }
    return $title;
}

function ecos_cactus_desktop_finder_builder_prototype_get_views($row, $url)
{
    parse_str($row['filter_query'],$filter);
    $views_temp = array(
        'label'=>$row['filter_name'],
        'optional'=>'',
        'filter'=>$filter,
        'filter_id'=>$row['filter_id'],
        'addon'=>'_FILTER_POINT_',
        'custom'=>true,
        'href'=>$url,
    );
    return $views_temp;
}
