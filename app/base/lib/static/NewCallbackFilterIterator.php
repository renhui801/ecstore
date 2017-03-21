<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class NewCallbackFilterIterator extends FilterIterator
{
    protected $callback;

    public function __construct(Iterator $iterator, $callback, $params =null){
        $this->callback = $callback;
        $this->_params = $params;
        parent::__construct($iterator);
    }

    public function accept(){
        return call_user_func(
            $this->callback,
            $this->current(),
            $this->key(),
            $this->getInnerIterator(),
            $this->_params
        );
    }
}

