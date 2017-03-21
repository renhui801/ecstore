<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author afei, bryant
 */


class system_ctl_admin_crontab extends desktop_controller {

    var $workground = 'system.workground.setting';
    function index() {
        $params = array (
            'title' => app::get('desktop')->_('计划任务管理'),
            'use_buildin_recycle' => false,
            'use_buildin_refresh' => true,
            'actions' => array(),
	    );
        $this->finder('base_mdl_crontab', $params);
    }

    function edit($cron_id) {
        $model = app::get('base')->model('crontab');
        $cron = $model->dump($cron_id);
        $this->pagedata['cron'] = $cron;
        $this->page('admin/crontab/detail.html');

    }

    function save() {
        $this->begin('index.php?app=system&ctl=admin_crontab&act=index');
        $model = app::get('base')->model('crontab');
        if( $model->update(array('schedule'=>$_POST['schedule']),
                           array('id'=> $_POST['id']))) {
            $this->end(true, '保存成功');
        } else {
            $this->end(false, '保存失败');
        }
    }

    function exec($cron_id) {
        $this->begin('index.php?app=system&ctl=admin_crontab&act=index');
        $model = app::get('base')->model('crontab');
        $cron = $model->getRow('id', array('id'=>$cron_id));
        if(!$cron || (base_crontab_schedule::trigger_one($cron['id'])===false)) {
            $this->end(false, '执行失败');
        }
        $this->end(true, '执行成功');
    }

}