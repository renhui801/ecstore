<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author chenping<chenping@shopex.cn>
 * @version 2011-8-9 11:31
 */

class emailsubs_finder_emailcont{

    function __construct(&$app) {
        $this->app = $app;
    }

    var $column_edit = '编辑';
    function column_edit($row){
        return '<a href="index.php?app=emailsubs&ctl=admin_emailcont&act=showEdit&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['ec_id'].'" >'.$this->app->_('编辑').'</a>';
    }

    var $column_preview = '预览';
    function column_preview($row){
        return '<a href="index.php?app=emailsubs&ctl=admin_emailcont&act=preview&_finder[finder_id]='.$_GET['_finder']['finder_id'].'&p[0]='.$row['ec_id'].'" target="dialog::{title:\''.$this->app->_('邮件预览').'\',width:800,height:680}">'.$this->app->_('预览').'</a>';
    }
}
