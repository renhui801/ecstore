<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_archive_orders extends archive_model{
    var $has_tag = true;
    var $defaultOrder = array('createtime','DESC');
    var $has_many = array(
        'order_objects'=>'archive_order_objects:contrast:order_id^order_id',
        'order_pmt'=>'archive_order_pmt:contrast:order_id^order_id'
    );

    public function extra_search_info(){
        return array(
            'key'=>array(
                'column'=>'order_id',
                'label'=>'订单号',
            ),
            'time_column'=>'createtime',
        );
    }

    // 提取订单号中的时间，这里需要与订单生成规则保持一致，否则可能导致后台搜索不到订单
    public function document2time($order_id){
        $order_len = strlen($order_id);
        switch ($order_len) {
            case '14':
                return array(
                    'start' => strtotime(substr($order_id,0,10).'0000'),
                    'end' => strtotime(substr($order_id,0,10).'5959'),
                );
                break;
            case '15':
                return array(
                    'start' => strtotime('20'.substr($order_id,0,10).'00'),
                    'end' => strtotime('20'.substr($order_id,0,10).'59'),
                );
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * 通过会员的编号得到orders标准数据格式
     * @params string member id
     * @params string page number
     * @params array order status
     * @return array sdf 数据
     */
    public function fetchByMember($member_id, $nPage=1, $limit=10)
    {
        $limitStart = ($nPage-1) * $limit;
        $filter = array('member_id' => $member_id);

        $orderIds = app::get('b2c')->model('archive_orders_members')->getList('order_id', $filter, $limitStart, $limit, 'createtime DESC');
        $oids = array();
        foreach($orderIds as $v){
        }
        $sdf_orders = $this->getList('*', array('order_id'=>$oids), $limitStart, $limit, 'createtime DESC');
        // 生成分页组建
        $countRd = $this->count($filter);
        $total = ceil($countRd/$limit);
        $current = $nPage;
        $token = '';
        $arrPager = array(
            'current' => $current,
            'total' => $total,
            'token' => $token,
        );

        $subsdf = array('order_objects'=>array('*',array('order_items'=>array('*'))));
        foreach ($sdf_orders as &$arr_order)
        {
            $arr_order = $this->dump(array('order_id'=>$arr_order['order_id']), '*', $subsdf);
        }

        $arrdata['data'] = $sdf_orders;
        $arrdata['pager'] = $arrPager;
        return $arrdata;
    }

    /**
     * 返回订单字段的对照表
     * @params string 状态
     * @params string key value
     */
    public function trasform_status($type='status', $val)
    {
        switch($type){
            case 'status':
                $tmpArr = array(
                            'active' => app::get('b2c')->_('活动'),
                            'finish' => app::get('b2c')->_('完成'),
                            'dead' => app::get('b2c')->_('死单'),
                );
                return $tmpArr[$val];
            break;
            case 'pay_status':
                $tmpArr = array(
                            0 => app::get('b2c')->_('未付款'),
                            1 => app::get('b2c')->_('已付款'),
                            2 => app::get('b2c')->_('付款至担保方'),
                            3 => app::get('b2c')->_('部分付款'),
                            4 => app::get('b2c')->_('部分退款'),
                            5 => app::get('b2c')->_('已退款'),
                );
                return $tmpArr[$val];
            break;
            case 'ship_status':
                $tmpArr = array(
                            0 => app::get('b2c')->_('未发货'),
                            1 => app::get('b2c')->_('已发货'),
                            2 => app::get('b2c')->_('部分发货'),
                            3 => app::get('b2c')->_('部分退货'),
                            4 => app::get('b2c')->_('已退货'),
                );
                return $tmpArr[$val];
            break;
        }
    }


    /**
     * smarty 修改订单备注的显示
     * @param array 出入的设置参数
     * @return string remark、
     */
    public function get_order_remark_display($remark='')
    {
        $arr_remark = unserialize(trim($remark));
        $arr_mark = array();
        if ($arr_remark)
        {
            foreach ($arr_remark as $remark_info)
            {
                if (is_int($remark_info['add_time']))
                    $arr_mark[] = "Marked by ".$remark_info['op_name'].", " . $remark_info['mark_text'] . ", " . date('Y-m-d H:i:s', $remark_info['add_time']);
                else
                    $arr_mark[] = "Marked by ".$remark_info['op_name'].", " . $remark_info['mark_text'] . ", " . $remark_info['add_time'];
            }
        }

        return $arr_mark;
    }

    /**
     * 得到特定订单的所有日志
     * @params string order id
     * @params int page num
     * @params int page limit
     * @return array log list
     */
    public function getOrderLogList($order_id, $page=0, $limit=-1)
    {
        $obj_orderloglist = kernel::service('b2c_change_orderloglist');
        $logisticst = app::get('b2c')->getConf('system.order.tracking');
        if(!is_object($obj_orderloglist) || $logisticst == 'false')
        {
            $objlog = $this->app->model('archive_order_log');
            $arrlogs = array();
            $arr_returns = array();

            if ($limit < 0)
            {
                $arrlogs = $objlog->getList('*', array('rel_id' => $order_id));
            }

            $limitStart = $page * $limit;

            $arrlogs_all = $objlog->getList('*', array('rel_id' => $order_id));
            $arrlogs = $objlog->getList('*', array('rel_id' => $order_id), $limitStart, $limit);
            if ($arrlogs)
            {
                foreach ($arrlogs as &$logitems)
                {
                    switch ($logitems['behavior'])
                    {
                        case 'creates':
                            $logitems['behavior'] = app::get('b2c')->_("创建");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }
                            }
                            break;
                        case 'updates':
                            $logitems['behavior'] = app::get('b2c')->_("修改");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }
                            }
                            break;
                        case 'payments':
                            $logitems['behavior'] = app::get('b2c')->_("支付");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2]);
                                }
                            }
                            break;
                        case 'refunds':
                            $logitems['behavior'] = app::get('b2c')->_("退款");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }
                            }
                            break;
                        case 'delivery':
                            $logitems['behavior'] = app::get('b2c')->_("发货");
                            /** 处理日志中的语言包问题 **/
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
                                }
                            }
                            break;
                        case 'reship':
                            $logitems['behavior'] = app::get('b2c')->_("退货");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key'],$arr_log['data'][0],$arr_log['data'][1],$arr_log['data'][2],$arr_log['data'][3],$arr_log_text['data'][4],$arr_log['data'][5]);
                                }
                            }
                            break;
                        case 'finish':
                            $logitems['behavior'] =  app::get('b2c')->_("完成");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }
                            }
                            break;
                        case 'cancel':
                            $logitems['behavior'] = app::get('b2c')->_("作废");
                            if ($arr_log_text = unserialize($logitems['log_text']))
                            {
                                $logitems['log_text'] = '';
                                foreach ($arr_log_text as $arr_log)
                                {
                                    $logitems['log_text'] .= app::get('b2c')->_($arr_log['txt_key']);
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            }

            $arr_returns['page'] = count($arrlogs_all);
            $arr_returns['data'] = $arrlogs;

            return $arr_returns;
        }
        else
        {
            return $obj_orderloglist->getOrderLogList($order_id, $page, $limit, true);
        }

    }

    /**
     * 重写getList方法
     */
    public function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        $arr_list = parent::getList($cols,$filter,$offset,$limit,$orderType);
        $obj_extends_order_service = kernel::serviceList('b2c_order_extends_actions');
        if ($obj_extends_order_service)
        {
            foreach ($obj_extends_order_service as $obj)
                $obj->extend_list($arr_list);
        }
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($arr_list,'b2c_mdl_orders',__FUNCTION__);
        return $arr_list;
    }


    /**
     * filter字段显示修改
     * @params string 字段的值
     * @return string 修改后的字段的值
     */
    public function modifier_payment($row)
    {
        if ($row == '-1')
        {
            // 货到付款
            return app::get('b2c')->_('货到付款');
        }

        $obj_paymentmethod = app::get('ectools')->model('payment_cfgs');
        $arr_data = $obj_paymentmethod->getPaymentInfo($row);

        return $arr_data['app_name'] ? $arr_data['app_name'] : $row;
    }

    public function modifier_member_id($row)
    {
        if (is_null($row) || empty($row))
        {
            return app::get('ectools')->_('未知会员或非会员');
        }

        $login_name =  kernel::single('b2c_user_object')->get_member_name(null,$row); 
        if($login_name){
            return $login_name; 
        }else{
            return app::get('ectools')->_('未知会员或非会员');
        }
    }

    public function modifier_final_amount($row)
    {
        $currency = app::get('ectools')->model('currency');
        $filter = array('order_id' => $this->pkvalue);
        $tmp = $this->getList('currency', $filter);
        $arr_cur = $currency->getcur($tmp[0]['currency']);
        $row = $currency->changer_odr($row,$tmp[0]['currency'],false,true,$this->app->getConf('system.money.decimals'),$this->app->getConf('system.money.operation.carryset'));

        return $row;
    }

    //订单备注图标2011-11-30
    public function modifier_mark_type($row){
        $res_dir = app::get('b2c')->res_url;
        $row = '<img width="20" height="20" src="'.$res_dir.'/remark_icons/'.$row.'.gif">';
        return $row;
     }

    public function modifier_mark_text($row)
    {
        $arr_mark = $this->get_order_remark_display($row);
        $mark_text = "";
        if ($arr_mark)
        {
            foreach ($arr_mark as $str_mark)
            {
                $mark_text .= $str_mark . ", ";
            }
        }
        if ($mark_text)
            $mark_text = substr($mark_text, 0, strlen($mark_text)-2);

        return $mark_text;
    }

    function _filter($filter,$tableAlias=null,$baseWhere=null){
        if (isset($filter) && $filter && is_array($filter) && array_key_exists('member_login_name', $filter))
        {
            $obj_pam_account = app::get('pam')->model('account');
            $pam_filter = array(
                'login_name|has'=>$filter['member_login_name'],
            );
            $row_pam = $obj_pam_account->getList('*',$pam_filter);
            $arr_member_id = array();
            if ($row_pam)
            {
                foreach ($row_pam as $str_pam)
                {
                    $arr_member_id[] = $str_pam['account_id'];
                }
                $filter['member_id|in'] = $arr_member_id;
            }
            else
            {
                if ($filter['member_login_name'] == app::get('b2c')->_('非会员顾客'))
                    $filter['member_id'] = 0;
            }
            unset($filter['member_login_name']);
        }

        foreach(kernel::servicelist('b2c_mdl_orders.filter') as $k=>$obj_filter){
            if(method_exists($obj_filter,'extend_filter')){
                $obj_filter->extend_filter($filter);
            }
        }
        $info_object = kernel::service('sensitive_information');
        if(is_object($info_object)) $info_object->opinfo($filter,'b2c_mdl_orders',__FUNCTION__);
        $filter = parent::_filter($filter);
        return $filter;
    }

    /**
     * 重写订单导出方法
     * @param array $data
     * @param array $filter
     * @param int $offset
     * @param int $exportType
     */
    public function fgetlist_csv( &$data,$filter,$offset,$exportType =1 ){
        $limit = 100;
        $cols = $this->_columns();
        if(!$data['title']){
            $this->title = array();
            foreach( $this->getTitle($cols) as $titlek => $aTitle ){
                $this->title[$titlek] = $aTitle;
            }
            // service for add title when export
            foreach( kernel::servicelist('export_add_title') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'addTitle') ) {
                        $services->addTitle($this->title);
                    }
                }
            }
            $data['title'] = '"'.implode('","',$this->title).'"';
        }

        if(!$list = $this->getList(implode(',',array_keys($cols)),$filter,$offset*$limit,$limit))return false;

        #$data['contents'] = array();
        foreach( $list as $line => $row ){
            // service for add data when export
            foreach( kernel::servicelist('export_add_data') as $services ) {
                if ( is_object($services) ) {
                    if ( method_exists($services, 'addData') ) {
                        $services->addData($row);
                    }
                }
            }
            $rowVal = array();
            foreach( $row as $col => $val ){

                if( in_array( $cols[$col]['type'],array('time','last_modify') ) && $val ){
                   $val = date('Y-m-d H:i',$val);
                }
                if ($cols[$col]['type'] == 'longtext'){
                    if (strpos($val, "\n") !== false){
                        $val = str_replace("\n", " ", $val);
                    }
                }

                if( strpos( (string)$cols[$col]['type'], 'table:')===0 ){
                    $subobj = explode( '@',substr($cols[$col]['type'],6) );
                    if( !$subobj[1] )
                        $subobj[1] = $this->app->app_id;
                    $subobj = app::get($subobj[1])->model( $subobj[0] );
                    $subVal = $subobj->dump( array( $subobj->schema['idColumn']=> $val ),$subobj->schema['textColumn'] );
                    //ajx  订单导出用户名问题
                    if($subVal['contact'][$subobj->schema['textColumn']]){
                        $subVal[$subobj->schema['textColumn']] = $subVal['contact'][$subobj->schema['textColumn']];
                    }
                    $val = $subVal[$subobj->schema['textColumn']]?$subVal[$subobj->schema['textColumn']]:$val;
                }

                if( array_key_exists( $col, $this->title ) )
                    $rowVal[] = addslashes(  (is_array($cols[$col]['type'])?$cols[$col]['type'][$val]:$val ) );
            }
            $data['contents'][] = '"'.implode('","',$rowVal).'"';
        }
        return true;

    }
    function getTitle(&$cols){
        $title = array();
        foreach( $cols as $col => $val ){
            if( !$val['deny_export'] )
            $title[$col] = $val['label'].'('.$col.')';
        }
        return $title;
    }
}
