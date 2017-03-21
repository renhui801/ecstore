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
class groupactivity_finder_purchase
{
    
    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('desktop')->router();
        $this->purchase = $this->app->model('purchase');
    }//End 
    
    public $column_edit='操作';
    public $column_edit_width='110';
    public function column_edit($row){
        $row = $this->purchase->getList('*',array('act_id'=>$row['act_id']));
		$row = $row[0];
        return 
            ( ( $row['state']==='1' || $row['state']==='2' ) ?
            '<a href="'. $this->router->gen_url( array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'edit','act_id'=>$row['act_id']) ) .'" >'.app::get('gigt')->_('编辑').'</a>&nbsp;&nbsp;'
            : '') 
           .( 
                ($row['state']!=='4') ? '' :
                '<a target="dialog::{width:300,height:120,resizeable:false,title:\'友情提示！\'}" href="'. $this->router->gen_url( array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'pre_apply_succ','act_id'=>$row['act_id'],'status'=>'true') ) .'">成功</a>'
            ).'&nbsp;&nbsp;'
           .( 
                ($row['state']!=='4') ? '' : 
            '<a target="dialog::{width:300,height:120,resizeable:false,title:\'友情提示！\'}" href="'. $this->router->gen_url( array('app'=>'groupactivity','ctl'=>'admin_purchase','act'=>'pre_apply','act_id'=>$row['act_id'],'status'=>'false') ) .'">失败</a>'
        );
    }
    
    
    var $detail_basic = '查看';

    function detail_basic($id){
        $render = $this->app->render();
        
        
        $arr = $this->purchase->getList( 'gid,act_open',array('act_id'=>$id) );
        reset( $arr );
        $arr = current( $arr );
        $gid = $arr['gid'];
        $render->pagedata['purchase'] = $arr;
        
        
        $goods = app::get('b2c')->model('goods')->getList('*',array('goods_id'=>$gid));
		$goods = $goods[0];
        $render->pagedata['goods'] = $goods;
        
        $imageDefault = app::get('image')->getConf('image.set');
        $defaultImage = $imageDefault['M']['default_image'];
        $render->pagedata['defaultImage'] = $defaultImage;
        
        $render->pagedata['url'] = app::get('site')->router()->gen_url( array('app'=>'groupactivity','ctl'=>'site_cart','act'=>'index','arg0'=>$id,'full'=>'true') );
        return $render->fetch('admin/finder/purchase.html');
    }
}