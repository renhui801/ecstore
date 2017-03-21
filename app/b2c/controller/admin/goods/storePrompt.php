<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_goods_storePrompt extends desktop_controller{

    function index(){
        $this->finder(
            'b2c_mdl_goods_store_prompt',
            array(
                'actions'=> array(
                    array('label'=>app::get('b2c')->_('添加规则'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_goods_storePrompt&act=add','target'=>'dialog::{ title:\''.app::get('b2c')->_('添加库存提示规则').'\', width:700, height:400}'),
                ),
                'title'=>app::get('b2c')->_('库存提示规则管理'),
                'base_filter'=>array('is_def'=>'false'),
                'use_buildin_recycle'=>true
            )
        );
    }

    function add(){
        $prompt_id = $_GET['prompt_id'];
        if($prompt_id){
            $arrPrompt = app::get('b2c')->model('goods_store_prompt')->getList('*',array('prompt_id'=>$prompt_id));
            $prompt = $arrPrompt[0];
            $prompt['values'] = unserialize($prompt['values']);
        }else{
            $prompt = array('values'=>array(0,1,2));//默认有三个
        }
        $this->pagedata['prompt'] = $prompt;
        $this->page('admin/goods/goods_storePrompt.html');
    }

    function save(){
        $this->begin('index.php?app=b2c&ctl=admin_goods_storePrompt&act=index');
        $store_prompt_model = app::get('b2c')->model('goods_store_prompt');
        if(isset($_POST['default']) && $_POST['default'] == 1){
            $store_prompt_model->update(array('default'=>0),array('default'=>1));
        }

        if(isset($_POST['prompt_id']) && $_POST['prompt_id']){
            $data['prompt_id'] = $_POST['prompt_id'];
        }
        if(!empty($_POST['name'])){
            $flag = $store_prompt_model->getList('*',array('name'=>$_POST['name']));
            if($flag && !$data['prompt_id']){
                $this->end(false,app::get('b2c')->_('规则名称不能重复'));
            }
        }else{
            $this->end(false,app::get('b2c')->_('规则名称不能为空'));
        }

        if(is_array($_POST['values']) && !empty($_POST['values'])){
            foreach($_POST['values'] as $key=>$row){
                if($row['min'] === '' || $row['max'] === ''){
                    $this->end(false,app::get('b2c')->_('库存区间参数不能为空'));
                }
            }
        }else{
            $this->end(false,app::get('b2c')->_('库存区间参数不能为空'));
        }
        $data['name'] = $_POST['name'];
        $data['default'] = $_POST['default'];
        $data['order_by'] = intval($_POST['order_by']);
        $data['values'] = serialize($_POST['values']);
        $flag = $store_prompt_model->save($data);
        if($flag){
            $this->end(true,app::get('b2c')->_('操作成功'));
        }else{
            $this->end(false,app::get('b2c')->_('操作失败'));
        }
    }

}
