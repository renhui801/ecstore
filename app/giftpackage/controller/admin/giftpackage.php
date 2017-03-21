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


class giftpackage_ctl_admin_giftpackage extends desktop_controller{

    //礼包位中的商品id 用于生成html、
    private $_filter_goods = null;

    var $workground = 'giftpackage_ctl_admin_giftpackage';




    function __construct($app) {
        parent::__construct($app);
        $this->_request = kernel::single('base_component_request');
    }

    function index(){
        $this->finder('giftpackage_mdl_giftpackage',array(
                'title'=>app::get('giftpackage')->_('礼包'),
                'actions'=>array(
                        array('label'=>app::get('giftpackage')->_('添加礼包'),'icon'=>'add.gif','href'=>'index.php?app=giftpackage&ctl=admin_giftpackage&act=add','target'=>'_blank'),
                    )
                )
            );
    }

    public function add() {
        $this->_filter_goods = array('0');
        $this->pagedata['goods']['html'] = $this->get_goods(true);
        $this->_edit();
        $this->singlepage('admin/frame.html');
    }

    /**
     * 修改礼包
     **/
    public function edit() {
        $id = $this->_request->get_get('id');
        $arr = $this->app->model('giftpackage')->dump($id);
        if(intval($arr['limitbuy_count']) == 0){
            $arr['limitbuy_count'] = '';
        }
        if( $arr['goods'] ) {
            $html = null;
            if( is_array($arr['goods']) ) {
                foreach( $arr['goods'] as $key => $row ) {
                    $this->_filter_goods = explode(',',$row); //礼包位中的商品id
                    //获取礼包位商品信息
                    $html[$key] = $this->get_goods(true);
                }
            } else {
                $this->_filter_goods = explode(',',$arr['goods']);
                $html = $this->get_goods(true);
            }
            $this->pagedata['goods']['html'] = $html;
        }
        $arr['member_lv_ids'] = $arr['member_lv_ids'] ? explode(',',$arr['member_lv_ids']) : array();
        $this->pagedata['giftpackage'] = $arr;
        $this->_edit();
        $this->singlepage('admin/frame.html');
    }


    /*
     * 模板
     */
    private function _edit()
    {
        $this->pagedata['giftnum'] = range(1,12);
        //////////////////////////// 会员等级 //////////////////////////////
        $mMemberLevel = app::get('b2c')->model('member_lv');
        $this->pagedata['member_level'] = $mMemberLevel->getList('member_lv_id,name', array(), 0, -1, 'member_lv_id ASC');

        $this->pagedata['sections'] = array(
            array('file'=>'admin/basic.html','label'=>'基本设置'),
            array('file'=>'admin/goods.html','label'=>'商品设置'),
            array('file'=>'admin/intro.html','label'=>'礼包设置'),
        );

        #$this->giftpackage
        $this->pagedata['html'] = $this->get_html(true);

         //是否显示积分
        if( app::get('b2c')->getConf('site.get_policy.method')==1 ) $this->pagedata['show_score'] = 'false';
    }
    #End Func
    /**
     * 保存礼包
     **/
    public function toAdd() {
        $data = $this->_get_params();
        $o = $this->app->model('giftpackage');
        $this->begin();
        if (isset($_POST['giftpackage']['amount']) && $_POST['giftpackage']['amount']){
            if(floatval($_POST['giftpackage']['amount']) <= 0){
                $this->end( false,'礼包总价要输入正数！'  );
            }
        }
        if (isset($_POST['giftpackage']['score']) && $_POST['giftpackage']['score']){
            if(preg_match('/[^\d]/', $_POST['giftpackage']['score'])){
                $this->end( false,'礼包积分要输入整数！'  );
            }
        }
        if (isset($_POST['giftpackage']['store']) && $_POST['giftpackage']['store']){
            if(preg_match('/[^\d]/', $_POST['giftpackage']['store'])){
                $this->end( false,'礼包库存要输入整数！'  );
            }
        }
        if (isset($_POST['giftpackage']['weight']) && $_POST['giftpackage']['weight']){
            if(floatval($_POST['giftpackage']['weight']) <= 0){
                $this->end( false,'礼包重量要输入正数！'  );
            }
        }
        if (isset($_POST['giftpackage']['order']) && $_POST['giftpackage']['order']){
            if(preg_match('/[^\d]/', $_POST['giftpackage']['order']) || intval($_POST['giftpackage']['order']) < 0){
                $this->end( false,'礼包排序要输入非负整数！'  );
            }
        }
		if ($_POST['giftpackage']['type']=='1'&&$_POST['giftpackage']['repeat']=='false')
		{
			$arr_goods = explode(',',$data[$_POST['giftpackage']['type']]['goods']);
			if (count($arr_goods) < intval($_POST['giftpackage']['goods_count'])){
				$this->end( false,'选择的商品数目大于等于礼包选择的商品数目！'  );
			}
		}

        if( $o->save($data) )
            $this->end( true,'礼包添加成功！'  );
        else
            $this->end( false,'礼包添加失败！'  );
    }


    private function _get_params()
    {
        $arr = $this->_request->get_post();
        $giftpackage = $arr['giftpackage'];
        $dtime = $this->_request->get_post('_DTIME_');
        if($arr['stime'])
            $giftpackage['stime'] = strtotime( $arr['stime'].' '.$dtime['H']['stime'].':'.$dtime['M']['stime'] );
        if($arr['etime'])
            $giftpackage['etime'] = strtotime( $arr['etime'].' '.$dtime['H']['etime'].':'.$dtime['M']['etime'] );

        if($giftpackage['stime'] && $giftpackage['etime'] && $giftpackage['stime']>=$giftpackage['etime'] ) {
            $this->begin( );
            $this->end( false,'结束时间不可以小于或等于开始时间!' );
        }
        else if($giftpackage['etime'] && $giftpackage['etime']<time()){
            $this->begin( );
            $this->end( false,'结束时间不可以小于或等于当前时间!' );
        }

        $giftpackage['member_lv_ids'] = implode(',',(array)($giftpackage['member_lv_ids']));
        $giftpackage['goods'] = $giftpackage[$giftpackage['type']]['goods'];

        if( !$giftpackage['goods'] ) {
            $this->begin( );
            $this->end( false,'请选择礼包商品!' );
        } else if( is_array($giftpackage['goods']) ) {
            $onlyone = count($giftpackage['goods'])==1 ? true : false;
            foreach( $giftpackage['goods'] as $key => $row ) {
                if( empty($row) ) {
                    $this->begin( );
                    $this->end( false, ($onlyone ? '请选择礼包商品!!' : '礼包第'. ($key+1) .'个商品位商品信息为空!') );
                }
            }
        }

        //限购数量不能大于库存
        if( $giftpackage['limitbuy_count'] && $giftpackage['limitbuy_count']>$giftpackage['store'] ) {
            $this->begin( );
            $this->end( false, '限购数量不能大于库存！' );
        }


        if( $giftpackage['alluser']=='true' ) $giftpackage['member_lv_ids'] = '';
        else {
            if( empty($giftpackage['member_lv_ids']) ) {
                $this->begin( );
                $this->end( false, '请选择会员等级!' );
            }
        }

        if( empty($giftpackage['name']) ) {
            $this->begin( );
            $this->end( false, '礼品名称必填！！!' );
        }

        if( empty($giftpackage['amount']) ) {
            $this->begin( );
            $this->end( false, '礼品总价必填！！!' );
        }

        if( !isset($giftpackage['score']) ) {
            $this->begin( );
            $this->end( false, '礼品积分必填！!' );
        }

        if( empty($giftpackage['store']) ) {
            $this->begin( );
            $this->end( false, '礼品库存必填！!' );
        }
        /*if( $giftpackage['limitbuy_count']!='' && $giftpackage['limitbuy_count'] == 0 ) {
            $this->begin( );
            $this->end( false, '礼品单笔限购数量必填！!' );
        }*/


        #if( !$giftpackage['goods_count'] ) $giftpackage['goods_count'] = 1;
        $this->trim_finder_all( $giftpackage['goods'] );
        if( $giftpackage['type']==2 ) {
            $giftpackage['repeat']='false';
        }

        if( !$giftpackage['order'] ) $giftpackage['order'] = 50;
        #$this->begin();
        #$this->end( false,'礼包添加失败！'  ); //验证参数
        $giftpackage['limitbuy_count'] = intval($giftpackage['limitbuy_count']);
        $giftpackage['score'] = intval($giftpackage['score']);
        return $giftpackage;
    }
    #End Func

    /*
     * 去除finder _ALL_属性
     */
    public function trim_finder_all( &$arr )
    {
        if( is_array($arr) ) {
            foreach( $arr as &$val ) {
                $this->trim_finder_all( $val );
            }
        } else {
            if( strpos($arr,'_ALL_')!==false ) {
                if( !$this->_all_goods_id ) {
                    $a = app::get('b2c')->model('goods')->getList( 'goods_id',array() );
                    if( $a && is_array($a) ) {
                        $a = array_map('current',$a);
                        $this->_all_goods_id = implode(',',(array)$a);
                    }
                }
                $arr = $this->_all_goods_id;
            }
            $arr_goods_id = $arr;
            $filter = array( 'goods_id'=>explode(',',$arr_goods_id));
            $arr_goods = app::get('b2c')->model('goods')->getList( 'store,name,nostore_sell,marketable',$filter );
            foreach( $arr_goods as $row ) {
				if ($row['nostore_sell']) continue;
                if( isset($row['store']) && !(float)$row['store'] ) {
                    $this->begin( );
                    $this->end( false,'商品：'.$row['name'].'库存为0！' );
                }
                //增加商品下架判断 20111222 mabaineng
                if($row['marketable'] == 'false'){
                	$this->begin( );
                    $this->end( false,'商品：'.$row['name'].'已下架！' );
                }
            }
        }
    }
    #End Func


    /*
     * 返回礼包商品位html
     */
    public function get_html($return=false)
    {
        $this->pagedata['return_url'] = app::get('desktop')->router()->gen_url( array('app'=>'giftpackage','ctl'=>'admin_giftpackage','act'=>'get_goods') );
        if( !$return ) {
            $arr = $this->_request->get_post();
            $num = (int)$arr['num'];
            $num = $num ? $num : 1;
        } else {
            $num = $this->pagedata['giftpackage'] ? $this->pagedata['giftpackage']['goods_count'] : 1;
        }
        $this->pagedata['num'] = range(1,$num);
        $html = $this->fetch('admin/goods/template.html');

        if( $return ) return $html;
        else exit($html);

    }
    #End Func

    /*
     * return goods info
     */
    public function get_goods($return=false)
    {
        if( !$return ) {
            $arr = $data = $_POST['data'];
        } else {
            $arr = $this->_filter_goods;
        }
        $filter = array( 'goods_id'=>$arr );
        $arr_goods_list = app::get('b2c')->model('goods')->getList( '*',$filter );
        $imageDefault = app::get('image')->getConf('image.set');
        $this->pagedata['image_default_id'] = $imageDefault['M']['default_image'];

        $this->pagedata['goods'] = $arr_goods_list;
        $html = $this->fetch('admin/goods/goods.html');
        if( $return ) return $html;
        else exit($html);
    }
    #End Func


}
