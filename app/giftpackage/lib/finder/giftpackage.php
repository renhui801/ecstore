<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class giftpackage_finder_giftpackage {

    function __construct(&$app) 
    {
        $this->app = $app;
        $this->router = app::get('site')->router();
    }//End 
    
    public $column_edit='编辑';
    public $column_edit_width='80';
    public function column_edit($row){
        return '<a href="index.php?app=giftpackage&ctl=admin_giftpackage&act=edit&id=' . $row['id'] . '" target="_blank" >'.app::get('gigt')->_('编辑').'</a>&nbsp;&nbsp;'.
              '<a href="'. $this->router->gen_url(array('app'=>'giftpackage', 'ctl'=>'site_giftpackage', 'act'=>'index', 'arg0'=>$row['id'])) . '" target="_blank" >'.app::get('gift')->_('预览').'</a>';
    }




}
