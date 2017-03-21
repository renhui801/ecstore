<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_finder_member_lv{    
    var $column_edit = '编辑';
    function column_edit($row){
        $return = '<a href="index.php?app=b2c&ctl=admin_member_lv&act=addnew&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['member_lv_id'].'" target="dialog::{title:\''.app::get('b2c')->_('编辑会员等级').'\', width:680, height:250}">'.app::get('b2c')->_('编辑').'</a>';
        if(!$row['default_lv']){
            $target = '{onComplete:function(){if (finderGroup&&finderGroup[\'' . $_GET['_finder']['finder_id'] . '\']) finderGroup[\'' . $_GET['_finder']['finder_id'] . '\'].refresh();}}';
        $return .= ' | <a target="'.$target.'" href="index.php?app=b2c&ctl=admin_member_lv&act=setdefault&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['member_lv_id'].'">'.app::get('b2c')->_('设为默认').'</a>';
        }
        return $return;
        
    }  
}
