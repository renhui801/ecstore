<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class system_finder_crontab {

    var $column_control = '操作';
    var $column_control_order = 'HEAD';

    function __construct($app) {
        $this->app = $app;
    }

    function column_control($row) {
        return '<a href="index.php?app=system&ctl=admin_crontab&act=edit&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&p[0]=' . $row['id'] . '" target="dialog::{title:\'' . app::get('system')->_('编辑计划任务') . '\', width:680, height:250}">' . app::get('system')->_('编辑') . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="index.php?app=system&ctl=admin_crontab&act=exec&_finder[finder_id]=' . $_GET['_finder']['finder_id'] . '&p[0]=' . $row['id'] . '" >' . app::get('system')->_('执行') . '</a>';
    }

    function column_rule($row){
        var_dump($row);
        
    }
}
