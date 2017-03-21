<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class queue_consumer extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        ob_implicit_flush(1);
        $this->obj_queue = system_queue::instance();
        $this->num = 100;
    }


    public function testBatchPublish(){
        $n = $this->num;
        $queues = array('order', 'test', 'other');
        for($i=0; $i<$n; $i++){
            $rand = mt_rand(0,1);
            $queue = $queues[$rand];
            $this->obj_queue->publish($queues, 'system_tasks_test');
        }
    }

    public function testSleep(){
        sleep(10);
        
    }
    

    public function testConsume(){
        $n = 2000;
        for($i=0; $i<$n; $i++){
            if ($queue_messgage = $this->obj_queue->get('other')) {
                $this->obj_queue->run_task($queue_messgage);
                echo $queue_messgage->get_id()."\n";
                $this->obj_queue->ack($queue_messgage);
            }
        }
    }
}



