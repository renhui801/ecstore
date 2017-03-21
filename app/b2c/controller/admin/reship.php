<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_ctl_admin_reship extends desktop_controller{

    var $workground = 'b2c_ctl_admin_order';
    
    /**
     * 构造方法
     * @params object app object
     * @return null
     */
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->finder('b2c_mdl_reship',array(
            'title'=>app::get('b2c')->_('退货单'),
            'allow_detail_popup'=>true,
            'use_buildin_recycle'=>false,
            'use_view_tab'=>true,
            'params'=>array(
                'bill_type' => 'reship',
            )
            ));
    }

    /**
     * 退货单view 列表
     * @param null
     * @return null
     */
    public function _views(){
        $mdl_reship = $this->app->model('reship');
        $sub_menu = array(
            0=>array('label'=>app::get('b2c')->_('退货中'),'optional'=>false,'filter'=>array('status'=>array('progress'),'disabled'=>'false')),
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
                    $show_menu[$k]['addon'] = $mdl_reship->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=b2c&ctl=admin_reship&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

    function addnew(){
        echo __FILE__.':'.__LINE__;
    }

}
