<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class weixin_menus {

    /**
    * @var array 节点数组
    * @access private
    */
    private $_menu_objects = array();
    /**
    * @var array 节点数组
    * @access private
    */
    private $_menu_maps = array();

    private $_all_menus = null;

    /**
    * 构造方法,实例化MODEL
    */
    function __construct() 
    {
        $this->model = app::get('weixin')->model('menus');
    }//End Function

    /**
    * 父节点下的子节点数据
    * @param int $parent_id 父节点id
    * @return 节点数组值
    */
    public function get_menus($parent_id=0, $bind_id=null, $menu_theme=1)
    {
        $parent_id = intval($parent_id);
        if(is_null($this->_all_menus)){
            $this->_all_menus = array();
            $filter = array('bind_id'=>$bind_id,'menu_theme'=>$menu_theme);
            $menus = app::get('weixin')->model('menus')->getList('*',$filter,0,-1,'ordernum ASC,menu_id ASC');
            foreach($menus AS $menu){
                $this->_all_menus[$menu['parent_id']][] = $menu;
            }
        }
        return $this->_all_menus[$parent_id];
    }//End Function

    /**
    * 节点的map
    * @param int $menu_id 节点id
    * @param int $setp 路径
    * @return array 节点路由
    */
    public function get_maps($menu_id=0, $step=null, $bind_id=null, $menu_theme=1) 
    {
        $step_key = (is_null($step)) ? 'all' : $step;

        $rows = $this->get_menus($menu_id, $bind_id, $menu_theme);
        $step = ($step==null) ? $step : $step-1;
        foreach($rows AS $k=>$v){
            if($v['has_children']=='true' && ($step==null || $step>=0)){
                $rows[$k]['childrens'] = $this->get_maps($v['menu_id'], $step, $bind_id, $menu_theme);
            }
        }
        $this->_menu_maps[$menu_id][$step_key] = $rows;

        return $this->_menu_maps[$menu_id][$step_key];
    }//End Function

    /**
    * 获取节点的map
    * @param string $menu_id
    * @param int $setp 路径
    * @param array
    */
    public function get_selectmaps($menu_id=0, $step=null, $bind_id=null, $menu_theme=1) 
    {
        $rows = $this->get_maps($menu_id, $step, $bind_id, $menu_theme);
        return $this->parse_selectmaps($rows);
    }//End Function

    /**
    * 获取节点的map
    * @param string $menu_id
    * @param int $setp 路径
    * @param array
    */
    public function get_listmaps($menu_id=0, $step=null, $bind_id=null, $menu_theme=1)
    {
        $rows = $this->get_maps($menu_id, $step, $bind_id, $menu_theme);
        return $this->parse_listmaps($rows);
    }//End Function

    /**
    * 格式化节点的map 是否首页，标题名
    * @param array $rows 节点MAP
    * @param array
    */
    private function parse_selectmaps($rows) 
    {
        $data = array();
        foreach((array)$rows AS $k=>$v){
            $data[] = array('menu_id'=>$v['menu_id'],'step'=>$v['menu_depth'], 'menu_name'=>$v['menu_name']);
            // if($v['childrens']){
            //     $data = array_merge($data, $this->parse_selectmaps($v['childrens']));
            // }
        }
        return $data;
    }//End Function
    
    /**
    * 格式化节点的map 是否首页，标题名
    * @param array $rows 节点MAP
    * @param array
    */
    private function parse_listmaps($rows) 
    {
        $data = array();
        foreach((array)$rows AS $k=>$v){
            $children = $v['childrens'];
            if(isset($v['childrens']))  unset($v['childrens']);
            $data[] = $v;
            if($children){
                $data = array_merge($data, $this->parse_listmaps($children));
            }
        }
        return $data;
    }//End Function

    /**
    * 获取节点的map
    * @param string $menu_id
    * @param int $setp 路径
    * @param array
    */
    public function get_weixin_menu($menu_id=0, $step=null, $bind_id=null, $menu_theme=1)
    {
        if(!$data = $this->get_data($menu_id, $step, $bind_id, $menu_theme)){
            return false;
        }

        foreach ( (array)$data as $k => $d ) {
            if ($d['parent_id'] != 0)
                continue;
            $tree ['button'] [$d['menu_id']] = $this->_deal_data ( $d );
            unset ( $data [$k] );
        }
        foreach ( $data as $k => $d ) {
            $tree ['button'] [$d ['parent_id']] ['sub_button'] [] = $this->_deal_data ( $d );
            unset ( $data [$k] );
        }
        $tree2 = array ();
        $tree2 ['button'] = array ();
        
        foreach ( $tree ['button'] as $k => $d ) {
            $tree2 ['button'] [] = $d;
        }
        return $tree2;
    }//End Function

    function get_data($menu_id=0, $step=null, $bind_id=null, $menu_theme=1) {
        $filter = array('bind_id'=>$bind_id,'menu_theme'=>$menu_theme);
        if(!$list = app::get('weixin')->model('menus')->getList('*',$filter,0,-1,'ordernum ASC,menu_id ASC')){
            return false;
        }

        // 取一级菜单
        foreach ( (array)$list as $k => $vo ) {
            if ($vo ['parent_id'] != 0)
                continue;
            
            $one_arr [$vo ['menu_id']] = $vo;
            unset ( $list [$k] );
        }
        
        foreach ( (array)$one_arr as $p ) {
            $data [] = $p;
            
            $two_arr = array ();
            foreach ( $list as $key => $l ) {
                if ($l ['parent_id'] != $p ['menu_id'])
                    continue;

                $two_arr [] = $l;
                unset ( $list [$key] );
            }
            
            $data = array_merge ( $data, $two_arr );
        }
        
        return $data;
    }
    function _deal_data($d) {
        $res ['name'] = $d['menu_name'];
        if($d['content_type']=='msg_url'){
            $res ['type'] = 'view';
            $res ['url'] = $d['msg_url'];
        } elseif($d['content_type']=='msg_text'){
            $res ['type'] = 'click';
            $res ['key'] = 'text_'.$d['msg_text'];
        }elseif($d['content_type']=='msg_image'){
            $res ['type'] = 'click';
            $res ['key'] = 'image_'.$d['msg_image'];
        }
        return $res;
    }

    // 需要绑定才能查看的链接
    function get_auth_link($appid, $eid){
        $genauthurl = kernel::single('weixin_wechat');
        $auth_module = $this->auth_module();
        foreach($auth_module as &$value){
            $value['url'] = $genauthurl->gen_auth_link( $appid, $eid, urlencode($value['url']) );
        }
        array_unshift($auth_module, array('label' => '--请选择--','url' => '' ) );
        return $auth_module;
    }

    public function auth_module(){
        $genurl = app::get('wap')->router();
        $auth_module = array(
            'member-index' => array(
                'label' => '会员中心',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'index', 'full'=>1 ) )
            ),
            'member-orders' => array(
                'label' => '我的订单',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'orders', 'full'=>1 ) )
            ),
            'member-favorite' => array(
                'label' => '收藏商品',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'favorite', 'full'=>1 ) )
            ),
            'member-security' => array(
                'label' => '修改密码',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'security', 'full'=>1 ) )
            ),
            'member-deposit' => array(
                'label' => '预存款充值',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'deposit', 'full'=>1 ) )
            ),
            'member-balance' => array(
                'label' => '预存款充值交易记录',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'balance', 'full'=>1 ) )
            ),
            'member-point_history' => array(
                'label' => '历史积分',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'point_history', 'full'=>1 ) )
            ),
            'member-receiver' => array(
                'label' => '地址管理',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'receiver', 'full'=>1 ) )
            ),
            'member-coupon' => array(
                'label' => '我的优惠券',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'coupon', 'full'=>1 ) )
            ),
            'member-nodiscuss' => array(
                'label' => '未评价商品',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_member', 'act'=>'nodiscuss', 'full'=>1 ) )
            ),
            'passport-bindstatus' => array(
                'label' => '微信号绑定状态管理页面',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_passport', 'act'=>'bindstatus', 'full'=>1 ) )
            ),
            'passport-loginbind' => array(
                'label' => '登录绑定页面',
                'url' => $genurl->gen_url( array( 'app'=>'b2c', 'ctl'=>'wap_passport', 'act'=>'loginbind', 'full'=>1 ) )
            ),
        );
        return $auth_module;
    }

}//End Class
