<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_member_lv extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){

        $this->finder('b2c_mdl_member_lv',array(
            'title'=>app::get('b2c')->_('会员等级'),
            'actions'=>array(
                         array('label'=>app::get('b2c')->_('添加会员等级'),'href'=>'index.php?app=b2c&ctl=admin_member_lv&act=addnew','target'=>'dialog::{width:680,height:250,title:\''.app::get('b2c')->_('添加会员等级').'\'}'),
                        )
            ));
    }

    function addnew($member_lv_id=null){



            $aLv['default_lv_options'] = array('1'=>app::get('b2c')->_('是'),'0'=>app::get('b2c')->_('否'));
            $aLv['default_lv'] = '0';
            $aLv['lv_type_options'] = array('retail'=>app::get('b2c')->_('普通零售会员等级'),'wholesale'=>app::get('b2c')->_('批发代理会员等级'));
            $aLv['lv_type'] = 'retail';
            $this->pagedata['levelSwitch']= $this->app->getConf('site.level_switch');
            $this->pagedata['lv'] = $aLv;

            if($member_lv_id!=null){
                $mem_lv = $this->app->model('member_lv');
                $aLv = $mem_lv->dump($member_lv_id);
                  $aLv['default_lv_options'] = array('1'=>app::get('b2c')->_('是'),'0'=>app::get('b2c')->_('否'));
              $this->pagedata['lv'] = $aLv;
            }

            $obj_ext_fields = kernel::servicelist('b2c_ext_member_level_field');
            if ($obj_ext_fields)
            {
                $site_point_expired = $this->app->getConf('site.point_expired');
                $site_point_expried_method = $this->app->getConf('site.point_expried_method');
                foreach ($obj_ext_fields as $obj_ext_service)
                {
                    $this->pagedata['ext_html'] = $obj_ext_service->get_html($site_point_expired, $site_point_expried_method, $this->pagedata['lv']);
                }
            }

            $this->display('admin/member/lv.html');
    }

    function save(){
        $this->begin();
        $objMemLv = $this->app->model('member_lv');
        if($objMemLv->validate($_POST,$msg)){
            if($_POST['member_lv_id']){
                $olddata = app::get('b2c')->model('member_lv')->dump($_POST['member_lv_id']);
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            if($objMemLv->save($_POST)){
                #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                if($obj_operatorlogs = kernel::service('operatorlog.members')){
                    if(method_exists($obj_operatorlogs,'member_lv_log')){
                        $newdata = app::get('b2c')->model('member_lv')->dump($_POST['member_lv_id']);
                        $obj_operatorlogs->member_lv_log($newdata,$olddata);
                    }
                }
                #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                $this->end(true,app::get('b2c')->_('保存成功'));
            }else{
                $this->end(false,app::get('b2c')->_('保存失败'));
           }
        }else{
            $this->end(false,$msg);
        }
    }

    function setdefault($lv_id){
        $this->begin();
        $objMemLv = $this->app->model('member_lv');
        $difault_lv = $objMemLv->dump(array('default_lv'=>1),'member_lv_id');
        if($difault_lv){
            $result1 = $objMemLv->update(array('default_lv'=>0),array('member_lv_id'=>$difault_lv['member_lv_id']));
            if($result1){
                $result = $objMemLv->update(array('default_lv'=>1),array('member_lv_id'=>$lv_id));
                $msg = app::get('b2c')->_('默认会员等级设置成功');
            }else{
                $msg = app::get('b2c')->_('默认会员等级设置失败');
            }
        }else{
            $result = $objMemLv->update(array('default_lv'=>1),array('member_lv_id'=>$lv_id));
            $msg = app::get('b2c')->_('默认会员等级设置成功');
        }
        $this->end($result,$msg);

    }

}
