<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class ectools_ctl_admin_payment extends desktop_controller{

    var $certcheck = false;

    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->finder('ectools_mdl_payments',array(
            'title'=>app::get('ectools')->_('收款单'),
            'use_buildin_recycle'=>false,
            'allow_detail_popup'=>true,
            'use_view_tab'=>true,
        ));
    }

    /**
     * 付款单view 列表
     * @param null
     * @return array
     */
    public function _views(){
        $mdl_payments = $this->app->model('payments');
        $sub_menu = array(
            0=>array('label'=>app::get('b2c')->_('失败'),'optional'=>false,'filter'=>array('status'=>array('failed'),'disabled'=>'false')),
            1=>array('label'=>app::get('b2c')->_('已支付到担保方'),'optional'=>false,'filter'=>array('status'=>array('progress'),'disabled'=>'false')),
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
                    $show_menu[$k]['addon'] = $mdl_payments->count($v['filter']);
                }
                $show_menu[$k]['href'] = 'index.php?app=ectools&ctl=admin_payment&act=index&view='.($k).(isset($_GET['optional_view'])?'&optional_view='.$_GET['optional_view'].'&view_from=dashboard':'');
            }elseif(($_GET['view_from']=='dashboard')&&$k==$_GET['view']){
                $show_menu[$k] = $v;
            }
        }
        return $show_menu;
    }

    /** 新建支付订单
     * @params array - 订单详细内容
     * @return boolean - 订单成功与否
     */
    public function addnew($arrPayments=array())
    {
        echo __FILE__.':'.__LINE__;
    }

}
