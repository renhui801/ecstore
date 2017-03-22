<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/21 0021
 * Time: 下午 5:17
 */
class notebook_ctl_default extends base_controller{
    public function index(){
        $this->pagedata['items']=$this->app->model('item')->getlist('*');
        $this->display('default.html');
    }

    public function addnew(){
        $this->begin(array('ctl'=>'default','act'=>'index',));
        $data=array(
            'item_subject'=>$_POST['subject'],
            'item_content'=>$_POST['content'],
            'item_email'=>$_POST['email'],
            'item_posttime'=>time(),
        );
        $result=$this->app->model('item')->insert($data);
        $this->end($result);
    }

}