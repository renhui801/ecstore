<?php
class apiactionlog_router_refund extends b2c_apiv_extends_request{

    var $method = 'store.trade.refund.add';
    var $callback = array();
    var $title = '订单退货单添加';
    var $timeout = 1;
    var $async = true;
}    

