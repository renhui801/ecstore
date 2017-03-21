<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class groupactivity_ctl_admin_purchase extends desktop_controller
{


    function __construct($app) {
        parent::__construct($app);
        $this->router = app::get('desktop')->router();
        $this->_request = kernel::single('base_component_request');
    }

    /*
     * Index
     */
    public function index()
    {
        $o = kernel::single("groupactivity_purchase");
        $o->edit_state();
        $o->edit_name();
        $this->finder('groupactivity_mdl_purchase',array(
                'title'=>app::get('groupactivity')->_('团购'),
                'actions'=>array(
                        array('label'=>app::get('groupactivity')->_('添加团购活动'),'icon'=>'add.gif','href'=>'index.php?app=groupactivity&ctl=admin_purchase&act=add'),
                    )
                )
            );
    }
    #End Func


    /*
     * 添加团购
     */
    public function add()
    {
        $this->_edit();

        $this->page('admin/add.html');
    }
    #End Func



    /*
     * 修改团购信息
     */
    public function edit()
    {
        $this->_edit();
        $id = $this->_request->get_get('act_id');
        $arr = $this->app->model('purchase')->getList('*',array('act_id'=>$id));
        $arr = $arr[0];
        $arr['member_lv_ids'] = $arr['member_lv_ids'] ? explode(',',$arr['member_lv_ids']) : array();

        $arr_goods = app::get('b2c')->model('goods')->getList( '*',array('goods_id'=>$arr['gid']) );
        if( $arr_goods && is_array($arr_goods) ) {
            reset( $arr_goods);
            $arr_goods = current( $arr_goods );
            $this->pagedata['goods'] = $arr_goods;
        }

        $this->pagedata['purchase'] = $arr;
        $this->page('admin/add.html');
    }
    #End Func

    /*
     * return goods info
     */
    public function get_goods_info()
    {
        $data = $_POST['data'];
        $arr = app::get('b2c')->model('goods')->getList( '*',array('goods_id'=>$data[0]) );
        reset( $arr );
        $arr = current($arr);
        echo json_encode( array('name'=>$arr['name'],'price'=>$arr['price'],'store'=>(INT)$arr['store'],'goods_id'=>$arr['goods_id'],'image'=>$arr['image_default_id'], 'brief'=>$arr['brief']) );
    }


    #End Func
    private function _edit()
    {
        $this->pagedata['return_url'] = $this->router->gen_url( array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'get_goods_info') );
        $this->pagedata['from_submit_url'] = $this->router->gen_url(array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'toAdd'));

        //添加的时候设置默认值
        if( !$this->pagedata['groupactivity'] )
        {
            $this->pagedata['purchase']['max_buy'] = 0;
            $this->pagedata['purchase']['pro_type'] = '2';
            $this->pagedata['purchase']['act_open'] = 'false';
        }


        //邮费优惠 to input radio
        $this->pagedata['data']['pro_type'] = array(
                                            '2'=>'无邮费优惠',
                                            '1'=>'达到一定购买量免运费',
                                        );
        //活动开启状态 to input radio
        $this->pagedata['data']['act_open'] = array(
                                            'true'=>'开启',
                                            'false'=>'关闭',
                                        );

        //是否显示积分
        if( app::get('b2c')->getConf('site.get_policy.method')==1 ) $this->pagedata['show_score'] = 'false';


        //会员等级
        $mMemberLevel = app::get('b2c')->model('member_lv');
        $this->pagedata['member_level'] = $mMemberLevel->getList('member_lv_id,name', array(), 0, -1, 'member_lv_id ASC');

    }
    #End Func

    /*
     * 保存团购信息
     */
    public function toAdd()
    {
        //print_r($this->_get_data());exit;
        $this->begin($this->router->gen_url(array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'index')));
        $data = $this->_get_data();
        $o = $this->app->model('purchase');
        $aGoods = app::get('b2c')->model('goods')->getList( 'store,nostore_sell',array('goods_id'=>$data['gid']) );
        $aGoods = $aGoods[0];
        $arr = $o->getList( '*',array('gid'=>$data['gid'],'end_time|than'=>time()) );
        if( $arr && !$data['act_id'] ) {
            $this->end( false,'该商品已在团购列表中！!');
        }
        if( $data['start_time']>=$data['end_time'] ) {
            $this->end( false,'结束时间不能小于或等于开始时间' );
        }
        if( $data['start_value']>=$data['max_buy'] && $data['max_buy']!=0 ) {
            $this->end( false,'活动最大数量应大于初始销售量' );
        }
        if( isset( $aGoods['store'] ) && !$aGoods['nostore_sell'] ){//无库存状态应该判断可以购买
            if( $aGoods['store']+$data['buy'] < $data['min_buy'] ){
                $this->end( false,'商品库存小于最小购买量' );
            }
            if( $aGoods['store']+$data['buy'] < $data['max_buy'] ){
                $this->end( false,'商品库存小于最大购买量' );
            }
        }

        if( $data['end_time']<=time() ) {
            $this->end( false,'结束时间小于或等于当前时间！' );
        }

        if( $data['max_buy']>0 && $data['max_buy']<$data['min_buy'] ) {
            $this->end( false,'活动最多数量小于最少数量！' );
        }
        $data['price'] = (float)$data['price'];

        if( $data['price']==0 ) {
            $this->end( false,'团购商品价格错误！！' );
        }

        if( $data['limit']>$data['max_buy'] && $data['max_buy']!=0 ) {
            $this->end( false,'限购数量大于活动最多数量！' );
        }
        if($data['alluser']=="true"){
            if( empty($data['orderlimit']) && $data['orderlimit']!=='' ) {
                $this->end( false,'每单限购数量不能为0！' );
            }
            $data['userlimit'] = "";
        }else{
            if(!$data['member_lv_ids']) {
                $this->end(false, '至少选择一个会员级别');
            }
            if( empty($data['userlimit']) && $data['userlimit']!=='' ) {
                $this->end( false,'每人限购数量不能为0！' );
            }
            $data['orderlimit']="";
        }

        if( empty($data['orderlimit']) ) $data['orderlimit'] = null;
        if( empty($data['userlimit']) ) $data['userlimit'] = null;

        if( $data['score'] === '' ) $data['score'] = null;
        if($data['pro_type'] == '1'){
            if( !preg_match('/^\d+$/', $data['postage'])){
                $this->end( false,'邮费优惠，单笔订单数必须为0或者正整数！');
            }
        }else{
            if( !$data['postage'] ) unset($data['postage']);
        }


        if( $data['end_time']<=time() ) $data['state'] = '5';
        if($data['max_buy']==$data['start_value']) $data['state'] = '4';
        $data['last_modified'] = time();
        $flag = $o->save($data);
        if( $flag ) {
            $arr = array('time'=>$data['end_time'],'act_id'=>$data['act_id']);
            kernel::single("groupactivity_purchase")->set_edit_time($arr);
            $this->end( true,'操作成功!');
        } else
            $this->end( false,'操作失败!');
    }
    #End Func

    /*
     * return sdf data
     */
    private function _get_data()
    {
        $arr = $this->_request->get_post();

        $dtime = $this->_request->get_post('_DTIME_');
        $arr['start_time'] = strtotime( $arr['start_time'].' '.$dtime['H']['start_time'].':'.$dtime['M']['start_time'] );
        $arr['end_time'] = strtotime( $arr['end_time'].' '.$dtime['H']['end_time'].':'.$dtime['M']['end_time'] );

        $now = time();
        if( $arr['start_time']<=$now && $arr['end_time']>=$now && $arr['act_open']=='true' )
            $arr['state'] = 2;

        if( $arr['start_time']>=$now )
            $arr['state'] = 1;

        $arr['member_lv_ids'] = implode(',',(array)($arr['member_lv_ids']));
        return $arr;
    }
    #End Func

    public function pre_apply() {
        $arr = $this->_request->get_params(true);
        $act_id = $arr['act_id'];
        $this->pagedata['linkurl'] = $this->router->gen_url( array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'apply','act_id'=>$act_id,'status'=>'false') );
        echo $this->fetch('admin/purchase/dialog.html');
    }

    public function pre_apply_succ() {
        $arr = $this->_request->get_params(true);
        $act_id = $arr['act_id'];
        $this->pagedata['linkurl'] = $this->router->gen_url( array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'apply','act_id'=>$act_id,'status'=>'true') );
        echo $this->fetch('admin/purchase/succdialog.html');
    }


    public function apply()
    {

        $this->begin( $this->router->gen_url( array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'index' ) ) );
        $arr = $this->_request->get_params(true);
        $act_id = $arr['act_id'];
        $status = $arr['status'];
        if( !$act_id )
            $this->end( false,'参数错误！团购id!');

        $refer = array('true'=>'local','false'=>'local_group');
        $state = array('true'=>'3','false'=>'5');

        $arr_order_id = $this->app->model('order_act')->getList( 'order_id',array('act_id'=>$act_id) );
        if( $arr_order_id ) {
            $arr_order_id = array_map('current',$arr_order_id);
            $this->app->model('orders')->update( array('order_refer'=>$refer[$status]),array('order_id'=>$arr_order_id) );;
        }

        $this->app->model('purchase')->update( array('state'=>$state[$status]),array('act_id'=>$act_id) );
        $this->end(true,'操作成功');
    }
    #End Func


}
