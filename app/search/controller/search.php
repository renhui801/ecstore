<?php

class search_ctl_search extends desktop_controller {

    private $_conf_pref = 'service.';

    function index() {
        $this->finder('search_mdl_search', array(
            'title' =>  app::get('site')->_('索引管理'),
            'base_filter' => array(),
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => false,
            'use_buildin_export' => false,
            'use_buildin_selectrow'=>false,
       ));
    }

    //开启搜索
    function set_default() {
    	$this->begin('index.php?app=search&ctl=search&act=index');
        $method = $_GET['method'];
        $type = $_GET['type'];
        $name = $_GET['name'];
    	if($method == 'open'){
    	    app::get('search')->setConf('server.search_server',$name);
    	}else{
    	    app::get('search')->setConf('server.search_server','');
    	}
        $this->end(true, $this->app->_('保存成功'));
    }

    public function index_setting(){
      $this->begin();
      foreach($_POST as $index=>$row){
        $key = 'search_index_setting_'.$index;
        app::get('search')->setConf($key,$row);
      }
      $this->end(true, $this->app->_('保存成功'));
    }

    function widgets(){
        $this->pagedata['search_key'] = app::get('search')->getConf('search_key');
        $this->pagedata['associate_enabled'] = app::get('search')->getConf('associate_enabled');
        $this->page('config/widgets.html');
    }

    function save_widgets(){
      $this->begin();
      app::get('search')->setConf('search_key',$_POST['search_key']);
      app::get('search')->setConf('associate_enabled',$_POST['associate_enabled']);
      $this->end(true, $this->app->_('保存成功'));
    }


    function set_default_segment() {
        $filter = array();
        $config = app::get('base')->getConf('server.search_segment');
        $arr_search = app::get('base')->model('app_content')->getList('*', array(
                'content_type' => 'service',
                'content_name' => 'search_segment',
        ));
        foreach($arr_search AS $key=>$val){
            $arr_search[$key]['name'] = kernel::single($val['content_path'])->name;
        }
        $this->pagedata['search_name'] = $config ? $config : 'search_service_segment_cjk';
        $this->pagedata['arr_search'] = $arr_search;
        $this->page('search/index.html');

    }

    function save_segment() {
    	$this->begin('index.php?app=search&ctl=search&act=index');
        app::get('base')->setConf('server.search_segment', $_POST['select']);
        $this->end(true, $this->app->_('分词器保存成功'));
    }

    public function init_data(){
        $this->page('config/init.html');
    }

    public function init(){
        $this->associate_model = app::get('search')->model('associate');
        $this->associate_model->delete();
        $this->begin('index.php?app=search&ctl=search&act=init_data');
        $from_type = 'goods_cat';
        $this->cat_model = app::get('b2c')->model('goods_cat');
        $catSdf = $this->cat_model->getList('cat_id,cat_name');
        foreach($catSdf as $row){
            $filter = array(
                'words' => $row['cat_name'],
                'type_id' => $row['cat_id'],
                'from_type' => 'goods_cat',
                'last_modify' => time(),
            );
            $this->associate_model->save($filter);
        }
        $this->keyword_model = app::get('b2c')->model('goods_keywords');
        $goodsWordsSdf = $this->keyword_model->getList('keyword,goods_id');
        foreach($goodsWordsSdf as $row){
            if($row['keyword']){
                $filter = array(
                    'words' => $row['keyword'],
                    'type_id' => $row['goods_id'],
                    'from_type' => 'goods_keywords',
                    'last_modify' => time(),
                );
                $this->associate_model->save($filter);
            }
        }
        $this->end(true,app::get('search')->_('初始化成功'));
    }

    function start() {
        $default = $_GET['default'];
        $service = $_GET['service'];
        $default = 1;
        $content_name = $_GET['content_name'];
        app::get('base')->setConf($this->_conf_pref.$content_name, $service);
    }

    function reindex() {
        $type = $_GET['type'];
        $name = $_GET['name'];
        $this->begin();
        search_core::segment();
        foreach(kernel::servicelist($type) as $service){
            if(get_class($service) == $name){
                $status = $service->reindex($msg);
                break;
            }
            }
            if($status)
                $this->end(true, $this->app->_($msg));
            else
                $this->end(false, $this->app->_($msg));
    }

    function status() {
        $type = $_GET['type'];
        $name = $_GET['name'];
        $this->begin();
        search_core::segment();
        foreach(kernel::servicelist($type) as $service){
            if(get_class($service) == $name){
                $status = $service->status($msg);
                break;
    }
    }
    if($status)
        $this->end(true, $this->app->_($msg));
    else
        $this->end(false, $this->app->_($msg));
    }

    function optimize() {
        $type = $_GET['type'];
        $name = $_GET['name'];
        $this->begin();
        search_core::segment();
        foreach(kernel::servicelist($type) as $service){
            if(get_class($service) == $name){
                $status = $service->optimize($msg);
                break;
    }
    }
    if($status)
        $this->end(true, $this->app->_($msg));
    else
        $this->end(false, $this->app->_($msg));
    }

    function clear() {
        $type = $_GET['type'];
        $name = $_GET['name'];
        $this->begin();
        search_core::segment();
        foreach(kernel::servicelist($type) as $service){
            if(get_class($service) == $name){
                $status = $service->clear($msg);
                break;
        }
        }
        if($status)
            $this->end(true, $this->app->_($msg));
        else
            $this->end(false, $this->app->_($msg));
        }

        }
