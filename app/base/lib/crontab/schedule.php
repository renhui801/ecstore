<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * @author bryant
 */

class base_crontab_schedule{
    static public function trigger_one($cron_id){
        if ($cron = app::get('base')->model('crontab')->getRow('id, last', array('id' => $cron_id, 'enabled => true'))){
            $now = time();            
            if (($now - $cron['last'])<60) {
                trigger_error(app::get('base')->_('1分钟之内不能重复执行'), E_USER_ERROR);
            }

            //add_task
            $worker = $cron['id'];
            system_queue::instance()->publish('crontab:'.$worker, $worker);
            self::__log($cron_id, $now, 'add queue ok');
            app::get('base')->model('crontab')->update(array('last'=>$now), array('id' => $cron_id));
        }
    }

    static public function trigger_all(){
        $cronentries = self::__get_enabled_cronentries();
        ignore_user_abort(1);
        $now = time();
        $filter = array();
        foreach ($cronentries as $cron) {
            //            var_dump(base_crontab_parser::parse($cron['schedule'], $cron['last']));
            if ($now >= base_crontab_parser::parse($cron['schedule'], $cron['last'])) {
                //todo: base_queue::instance()->addTask()
                //todo: update 变更为一次性更新
                $worker = $cron['id'];
                system_queue::instance()->publish('crontab:'.$worker, $worker);
                $filter['id'][] = $cron['id'];
                self::__log($cron['id'], $now, 'add queue ok');
            }
        }
        if (!empty($filter)) {
            app::get('base')->model('crontab')->update(array('last'=>$now), $filter);
        }
    }


    static public function __get_enabled_cronentries($filter=array()){
        //todo: 增加缓存
        $filter = array_merge(array('enabled' => 'true'),
                              $filter);
        
        return app::get('base')->model('crontab')->getList('id, schedule, last', $filter);
    }
    
    /**
     * 检查是否为有效任务
     *
     * 只做单次处理. 如果是批量执行请勿用 
     * 
     * @param int $cron_id 任务ID
     * @return bool
     */
    static public function is_valid_cronentry($cron_id){
        
        $filter = array('id' => $cron_id);
        return app::get('base')->model('crontab')->getRow('id, schedule, last', $filter);
    }


    /**
     * 执行crontab任务
     *
     * 正常执行任务, 流程通过system_queue::run_task进行处理
     * 此函数目前只为测试执行单次任务服务, cmd & crontab后台执行命令
     * 
     * @param int $cron_id 任务ID
     * @return bool
     */
    static public function run_task($cron_id){ 
        //set_error_handler(array(self, 'error_handler')); 
        $add_time = time(); 
        $class_name = $cron_id; 
        $class = new $class_name(); 
        if ($class instanceof base_interface_task) {
            self::__log($cron_id, time(), 'run start');
            $class->exec();
            self::__log($cron_id, time(), 'run over');
            return true;
        }else{ 
            self::__log($cron_id, time(), 'run fail: cannot find the call class'); 
            return false; 
        } 
    }
     
    static private function __log($cron_id, $add_time, $msg){
        logger::info(sprintf("crontab task:%s add_time:%s | %s",
                             $cron_id,
                             //date("F j, Y, g:i a", $add_time),
                             date('Y-m-d H:m:i', $add_time),
                             $msg));
    }
}
