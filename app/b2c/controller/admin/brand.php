<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_brand extends desktop_controller{

    var $workground = 'b2c.workground.goods';


    function index(){
        $this->finder('b2c_mdl_brand',array(
            'title'=>app::get('b2c')->_('商品品牌'),
            'actions'=>array(
                array('label'=>app::get('b2c')->_('添加品牌'),'icon'=>'add.gif','href'=>'index.php?app=b2c&ctl=admin_brand&act=create','target'=>'_blank'),

            )
            ));
    }

    function getCheckboxList(){
        $brand = $this->app->model('brand');
        $this->pagedata['checkboxList'] = $brand->getList('brand_id,brand_name',null,0,-1);
        $this->page('admin/goods/brand/checkbox_list.html');
    }

    function create(){
        $oGtype = $this->app->Model('goods_type');
        $objBrand = $this->app->model('brand');
        $this->pagedata['type'] = $objBrand->getDefinedType();
        $this->pagedata['brandInfo']['type'][$this->pagedata['type']['default']['type_id']] = 1;
        $this->pagedata['gtype']['status'] = $oGtype->checkDefined();
        $this->singlepage('admin/goods/brand/detail.html');
    }

    function save(){
        $this->begin('index.php?app=b2c&ctl=admin_brand&act=index');
        $objBrand = $this->app->model('brand');
        $brandname = $objBrand->dump(array('brand_name'=>$_POST['brand_name'],'brand_id'));
        if(empty($_POST['brand_id']) && is_array($brandname)){
             $this->end(false,app::get('b2c')->_('品牌名重复'));
        }
        $_POST['ordernum'] = intval( $_POST['ordernum'] );
        $data = $this->_preparegtype($_POST);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
            $olddata = app::get('b2c')->model('brand')->dump($_POST['brand_id']);
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        if($objBrand->save($data)){
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.goods')){
                if(method_exists($obj_operatorlogs,'brand_log')){
                    $obj_operatorlogs->brand_log($_POST,$olddata);
                }
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            $this->end(true,app::get('b2c')->_('品牌保存成功'));
        }else{
            $this->end(false,app::get('b2c')->_('品牌保存失败'));
        }
    }

    function edit($brand_id){
        $this->path[] = array('text'=>app::get('b2c')->_('商品品牌编辑'));
        $objBrand = $this->app->model('brand');
        $this->pagedata['brandInfo'] = $objBrand->dump($brand_id);
        if(empty($this->pagedata['brandInfo']['brand_url'])) $this->pagedata['brandInfo']['brand_url'] = 'http://';

        foreach($objBrand->getBrandTypes($brand_id) as $row){
            $aType[$row['type_id']] = 1;
        }

        $this->pagedata['brandInfo']['type'] = $aType;
        $this->pagedata['type'] = $objBrand->getDefinedType();
        $objGtype = $this->app->model('goods_type');
        $this->pagedata['gtype']['status'] = $objGtype->checkDefined();
        $this->singlepage('admin/goods/brand/detail.html');
    }
    function _preparegtype($data){
        if(is_array($data['gtype'])){
            foreach($data['gtype'] as $key=>$val){
                $pdata = array('type_id'=>$val);
                $result[] = $pdata;
            }
        }
        $data['seo_info']['seo_title'] = $data['seo_title'];
        $data['seo_info']['seo_keywords'] = $data['seo_keywords'];
        $data['seo_info']['seo_description'] = $data['seo_description'];
        $data['seo_info'] = serialize($data['seo_info']);
        unset($data['seo_title']);
        unset($data['seo_keywords']);
        unset($data['seo_description']);
        $data['gtype'] = $result;
        return $data;
    }

}
