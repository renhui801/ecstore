<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_ctl_admin_refund extends desktop_controller{

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    public function index(){
        $this->finder('ectools_mdl_refunds',array(
            'title'=>app::get('ectools')->_('退款单'),
            'use_view_tab'=>true,
            'allow_detail_popup'=>true,
            'use_buildin_recycle'=>false,
        ));
    }

    /**
     * 退款单view 列表
     * @param null
     * @return null
     */
    public function _views(){
        $mdl_refunds = $this->app->model('refunds');
        $sub_menu = array(
            0=>array('label'=>app::get('b2c')->_('退款中'),'optional'=>false,'filter'=>array('status'=>array('progress'),'disabled'=>'false')),
            1=>array('label'=>app::get('b2c')->_('失败'),'optional'=>false,'filter'=>array('status'=>array('failed'),'disabled'=>'false')),
            2=>array('label'=>app::get('b2c')->_('成功'),'optional'=>false,'filter'=>array('status'=>array('succ'),'disabled'=>'false')),
            3=>array('label'=>app::get('b2c')->_('全部'),'optional'=>false,'filter'=>array('disabled'=>'false')),
        );

        if(isset($_GET['optional_view'])) $sub_menu[$_GET['optional_view']]['optional'] = false;

        foreach($sub_menu as $k=>$v){
            if($v['optional']==false){
                $show_menu[$k] = $v;
                if(is_array($v['filter'])){
                    $v['filter'] = array_merge(array(),$v['filter']);
                }else{
                    $v['filter'] = array();
                }
                $show_menu[$k]['filter'] = $v['filter']?$v['filter']:null;
                if($k==$_GET['view']){
                    $show_menu[$k]['newcount'] = true;
                    $show_menu[$k]['addon'] = $mdl_refunds->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=ectools&ctl=admin_refund&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

}
