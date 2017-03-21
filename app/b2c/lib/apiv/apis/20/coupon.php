<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c coupon优惠劵 interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_20_coupon
{

    /**
     * 获取优惠劵列表(CRM调用)
     */
    public function get_coupon_list($params,&$service){

        $couponModel = app::get('b2c')->model('coupons');
        $couponData = $couponModel->getList("*");

        $mSRO = app::get('b2c')->model('sales_rule_order');

        //会员等级
        $memberLvData = app::get('b2c')->model('member_lv')->getList('member_lv_id,name');
        foreach( (array)$memberLvData  as $lv_row){
            $memberLv[$lv_row['member_lv_id']] = $lv_row['name']; 
        }

        foreach( (array)$couponData as $k=>$row ){
            $aRule = $mSRO->getList('*',array('rule_id'=>$row['rule_id']));

            $data[$k]['cpns_id'] = intval($row['cpns_id']);
            $data[$k]['cpns_name'] = $row['cpns_name'];
            $data[$k]['cpns_prefix'] = $row['cpns_prefix'];
            $data[$k]['cpns_status'] = $row['cpns_status'];
            $data[$k]['cpns_type'] = ($row['cpns_type'] == '1') ? 'B' : 'A';
            $data[$k]['from_time'] = $aRule[0]['from_time'];
            $data[$k]['to_time'] = $aRule[0]['to_time'];
            //规则描述
            $data[$k]['description'] = $aRule[0]['description'];

            //优惠券会员等级
            $member_lv_ids = explode(',', $aRule[0]['member_lv_ids']);
            foreach( (array)$member_lv_ids  as $lv_id){
                $member_lv_data[] = $memberLv[$lv_id];
            }
            $data[$k]['member_lv'] = implode(',',$member_lv_data);

            //优惠条件
            if($aRule[0]['conditions'] && $aRule[0]['c_template'])
            {
                $data[$k]['conditions'] = kernel::single($aRule[0]['c_template'])->tpl_name;
            }

            //优惠方案
            if($aRule[0]['action_solution'] && $aRule[0]['s_template'])
            {
            	$o = kernel::single($aRule[0]['s_template']);
            	$o->setString($aRule[0]['action_solution'][$aRule[0]['s_template']]);
                $data[$k]['action_solution'] = $o->getString();
            }
        }
        return $data;
    }


    /**
     * 对指定会员发放指定的B类优惠券
     * @param array $params  num 数量，cpns_id优惠券ID，member_id 字符串以逗号隔开，会员ID
     * @return array $cpns_code 发放的优惠券号码
     */
    public function create_coupon_to_member($params, &$service){
        $params['num'] = $params['num'] ? intval($params['num']) : 1;

        $memberModel = app::get('b2c')->model('members');
        
        $memberids = explode(',',$params['member_id']);
        if(count($memberids) > 200 ){
            return $service->send_user_error('8008', '给会员发送优惠券会员不能多于200个');
        }

        if( empty($memberids) ){
            return $service->send_user_error('8006', '会员ID必填');
        }

        $cpns_num = count($memberids) * intval($params['num']);
        $cpns_code = $this->get_coupon_number( array('cpns_id'=>intval($params['cpns_id']),'num'=>$cpns_num) , $service);
        if( !$params['cpns_id'] || !$cpns_code )
        {
            return $service->send_user_error('8007', '获取优惠券失败');
        }

        if( count($cpns_code) != $cpns_num ){
            return $service->send_user_error('8007', '优惠券数量不够，请调低发送优惠券基数');
        }

        $tmp_cpns_code = $cpns_code;
        $memberCouponModel = app::get('b2c')->model('member_coupon');
        foreach( (array)$memberids as $member_id ){
            if (  !$memberModel->getList('member_id', array('member_id'=>$member_id)) )
            {
                return $service->send_user_error('8006', '会员不存在');
            }

            for( $i=0;$i<$params['num'];$i++ ){
                $arr_m_c = array();
                $arr_m_c['cpns_id'] = intval($params['cpns_id']);
                $arr_m_c['member_id'] = $member_id;
                $arr_m_c['memc_used_times'] = 0;
                $arr_m_c['memc_gen_time'] = time();
                $memc_code = array_pop($tmp_cpns_code);
                $arr_m_c['memc_code'] = $memc_code;
                $memberCouponModel->save($arr_m_c);
            }
        }

        return $cpns_code;
    }

    /**
     * 获取优惠券，只有B类优惠券需要调用
     * @param array $params array('num'=>'获取优惠券数量','cpns_id'=>'优惠券ID')
     */
    public function get_coupon_number($params, &$service){
        $params['num'] = $params['num'] ? intval($params['num']) : 50;

        if( intval($params['num']) > 1000 )
        {
            return $service->send_user_error('8001', '获取优惠券数量一次不能超过1000');
        }

        if(!$params['cpns_id']) 
        {
            return $service->send_user_error('8002', '该优惠券号码必填');
        }

        $couponsModel = app::get('b2c')->model('coupons'); 
        $couponData = $couponsModel->getList("*",array('cpns_id'=>intval($params['cpns_id'])));

        if(!$couponData[0]) 
        {
            return $service->send_user_error('8002', '该优惠券号码不存在');
        }

        if( !$couponData[0]['cpns_type'] )
        {
            return $service->send_user_error('8003', '该优惠券是A类优惠券，不需要获取优惠券号码');
        }

        if( !$couponData[0]['cpns_status'] )
        {
            return $service->send_user_error('8004', '该优惠券未启用');
        }

        if( intval($params['cpns_id']) )
        {
            $list = $couponsModel->downloadCoupon(intval($params['cpns_id']),$params['num']);
        }

        if( !$list )
        {
            return $service->send_user_error('8005', '当前优惠券时间未到');
        }
        return $list;
    }

    /**
     * 获取优惠券使用记录
     * 如果分页数为-1的时候则返回优惠券使用记录总条数统计
     * 如果指定优惠券ID 则返回指定优惠券的使用记录
     * @param array $params
     */
    public function get_coupon_use_log($params, &$service){

        $couponUseModel = app::get('couponlog')->model('order_coupon_user'); 

        //默认分页码为1,分页大小为20
        $params['page_no'] = is_int($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = is_int($params['page_size']) ? $params['page_size'] : 20;

        $page_no = intval($params['page_no']) - 1;
        $limit  = intval($params['page_size']);
        $offset = $page_no * $limit;

        //如果参数中指定优惠券则反正指定优惠券的使用记录
        $filter = array();
        if( $params['cpns_id'] )
        {
            $filter['cpns_id'] = intval($params['cpns_id']);
        }

        //返回总数
        $rows = $couponUseModel->count($filter);
        $data['item_total'] = intval($rows);

        $useLogData = $couponUseModel->getList('*',$filter,$offset,$limit);
        $data['list'] = array();
        foreach( (array)$useLogData as $k=>$row )
        {
            $data['list'][$k]['cpns_id']   = intval($row['cpns_id']);
            $data['list'][$k]['cpns_name'] = $row['cpns_name'];
            $data['list'][$k]['order_id']  = intval($row['order_id']);
            $data['list'][$k]['amount']    = $row['total_amount'];
            $data['list'][$k]['memc_code'] = $row['memc_code'];
            $data['list'][$k]['usetime']   = $row['usetime'];
            $data['list'][$k]['member_id'] = intval($row['member_id']);
        }
        return $data;
    }


}
