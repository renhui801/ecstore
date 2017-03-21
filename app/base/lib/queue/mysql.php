<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
class base_queue_mysql implements base_interface_queue
{
    private $queue;
    public function __construct()
    {
        $this->queue = app::get('base')->model('queue');
    }

    public function publish($message)
    {
        $data = $message;
        return $this->queue->insert($data);
    }
    
    public function consume()
    {
        $this->queue->flush();
    }
}
