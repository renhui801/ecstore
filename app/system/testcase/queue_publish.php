<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class queue_publish extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->obj_queue = system_queue::instance();
    }

    public function testPublish(){
        $this->obj_queue->publish('other', 'system_tasks_test');
    }

    public function testBatchPublish(){
        $n = 200;
        $queues = array('order', 'test', 'other');
        for($i=0; $i<$n; $i++){
            $rand = mt_rand(0,1);
            $queue = $queues[$rand];
            $this->obj_queue->publish($queues, 'system_tasks_test');
        }
    }
}



