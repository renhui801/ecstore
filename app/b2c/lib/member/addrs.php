<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_member_addrs{

    public function get_receive_addr(&$controller, $addr_id='', $member_id=0, $tpl='site/common/rec_addr.html'){
            if ($addr_id){
                $arr_addr_id = explode(':', $addr_id);
                $addr_id = $arr_addr_id[0];
                $last_addr_id = $arr_addr_id[1];

                if ($addr_id != '-1'){
                    $member_addrs = &$controller->app->model('member_addrs');
                    $arr_member_addr = $member_addrs->dump(array('addr_id'=>$addr_id ? $addr_id : $last_addr_id,'member_id'=>$member_id), '*');
                    if ($addr_id == '0' && $last_addr_id != '0'){
                        unset($arr_member_addr['addr_id']);
                    }
                }else{
                    if (!$member_id){
                        $arr = unserialize(stripslashes($_COOKIE['purchase']['addon']));
                        $arr_member_addr['addr_id'] = 0;
                        $arr_member_addr['member_id'] = 0;
                        $arr_member_addr['name'] = $arr['member']['ship_name'];
                        $arr_member_addr['area'] = $arr['member']['ship_area'];
                        $arr_member_addr['addr'] = $arr['member']['ship_addr'];
                        $arr_member_addr['zipcode'] = $arr['member']['ship_zip'];
                        $arr_member_addr['email'] = $arr['member']['ship_email'];
                        $arr_member_addr['day'] = $arr['member']['day'];
                        $arr_member_addr['specal_day'] = $arr['member']['specal_day'];
                        $arr_member_addr['time'] = $arr['member']['time'];
                        $arr_member_addr['phone'] = array(
                            'mobile'=>$arr['member']['ship_mobile'],
                            'telephone'=>$arr['member']['ship_tel'],
                        );
                    }else{
                        $obj_member = $controller->app->model('members');
                        $tmp = $obj_member->getList('addon', array('member_id' => $member_id));
                        $arr_member = $tmp[0];
                        $arr_addon = unserialize($arr_member['addon']);
                        $arr_member_addr = $arr_addon['def_addr'];
                        $arr_member_addr['zipcode'] = $arr_member_addr['zip'];
                        $arr_member_addr['phone'] = array(
                            'mobile' => $arr_member_addr['mobile'],
                            'telephone' => $arr_member_addr['tel'],
                        );
                    }
                    $controller->pagedata['has_last_def_addr'] = 1;
                }
            }

            $is_rec_addr_edit = 'true';
            $obj_recsave_checkbox = kernel::service('b2c.checkout_recsave_checkbox');
            $controller->pagedata['is_rec_addr_edit'] = $is_rec_addr_edit;
            $controller->pagedata['addr'] = $arr_member_addr;
            $controller->pagedata['address']['member_id'] = $member_id;
            $controller->pagedata['site_checkout_zipcode_required_open'] = $controller->app->getConf('site.checkout.zipcode.required.open');
            $controller->pagedata['is_recsave_display'] = $is_recsave_display;
            $str_html = $controller->fetch($tpl);
            $obj_ajax_view_help = kernel::single('b2c_view_ajax');
            return $obj_ajax_view_help->get_html($str_html, 'b2c_member_addrs','get_receive_addr');;
    }

    //获取订单确认页默认地址
    public function get_default_addr($member_id=null){
        $this->obj_session = kernel::single('base_session');
        $this->obj_session->start();
        $obj_member_addrs = app::get('b2c')->model('member_addrs');
        //缓存地址判断
        if($member_id && $_COOKIE['purchase']['addr']['usable'] && $_COOKIE['purchase']['addr']['usable'] == md5($this->obj_session->sess_id().$member_id)){
            $def_addr_id = $_COOKIE['purchase']['addr']['addr_id'];
            $tmp_def_addr = $obj_member_addrs->getList('*',array('addr_id'=>$def_addr_id));
            $tmp_def_addr ? $def_addr = $tmp_def_addr[0] : $def_addr = null;
        }else{
            $def_addr = null;
        }

        //常用地址是否有默认地址
        $tmp_cnt = $obj_member_addrs->getList('*',array('member_id'=>$member_id,'def_addr'=>'1'));
        if(!$def_addr && $tmp_cnt ) $def_addr = $tmp_cnt[0];

        //是否有会员绑定地址
        if(!$def_addr){
          $tmp_member_addon = app::get('b2c')->model('members')->getList('addon',array('member_id'=>$member_id));
          $tmp_member_addon ? $def_addr = $tmp_member_addon[0]['addon'] : $def_addr = null;
          $def_addr = unserialize($def_addr);
          if(!app::get('b2c')->model('members')->check_addr($def_addr['addr_id'],$member_id)){
            $def_addr = null;
          }
        }
        return $def_addr;
    }

  //保存修改编辑后的收货地址
	public function purchase_save_addr($arr_post=array(),$member_id,&$msg=''){
        //检查必填信息是否存在
        if( !$this->check_addr_post($arr_post,$member_id,$msg) ) return false;
		$arr_post = utils::_filter_input($arr_post);

		foreach($arr_post as $k=>$v){
			$arr_post[$k] = strip_tags($v);
		}

        $member_addrs_model = app::get('b2c')->model('member_addrs');
        /*需要存储的数据*/
        $save_data = array(
            'member_id' => $member_id,
            'area' => $arr_post['area'],
            'addr' => $arr_post['addr'],
            'name' => $arr_post['name'],
            'zip'  => $arr_post['zip'],
            'tel'  => $arr_post['tel'],
            'mobile' => $arr_post['mobile'],
            'time' => $arr_post['time'],
        );

        //指定送货时间
        $save_data['time']  = $arr_post['time'] ? $arr_post['time'] : '任意时间段';
        if($arr_post['day'] == 'special'){
            $save_data['day']  = $arr_post['special']; //指定日期
        }else{
            $save_data['day']  = $arr_post['day'] ? $arr_post['day'] : '任意日期';
        }
        /*end*/

        $row = $member_addrs_model->getList('addr_id',$save_data);
        if($row && $row[0]['addr_id'] != $arr_post['addr_id'] ){
            $msg = app::get('b2c')->_('收货地址重复');return false;
        }

        /*订单如果是新增收货地址，设置为默认地址
         *会员中心根据提交数据来是否设置为默认地址
         */
        if(!$arr_post['addr_id'] && !isset($arr_post['def_addr']) ){
            $save_data['def_addr'] = 1;
        }else{
            $save_data['def_addr']  = $arr_post['def_addr'];
            $addr_id = $save_data['addr_id'] = $arr_post['addr_id'];
        }

        //save
        if(!$update_addr_id = $member_addrs_model->set_default_addr($save_data,$addr_id,$member_id,$msg)){
            return false;
        }
        $save_data['addr_id'] = $update_addr_id;
        return $save_data;
	}

    //验证收货地址必填参数是否存在
    function check_addr_post($arr_post,$member_id,&$msg=''){
        if(empty($arr_post) || empty($member_id)){
            $msg = app::get('b2c')->_('缺少参数');
			return false;
        }
        if(!$arr_post['area']){
            $msg = app::get('b2c')->_('收货地区未选择');
			return false;
        }
        if(!$arr_post['addr']){
            $msg = app::get('b2c')->_('收货地址未填写');
			return false;
        }
        if(!$arr_post['name']){
            $msg = app::get('b2c')->_('收货人姓名未填写');
			return false;
        }
        if($arr_post['mobile'] && !preg_match("/^1[34578]{1}[0-9]{9}$/",$arr_post['mobile'])){
            $msg = app::get('b2c')->_('手机号码格式不正确');
            return false;
        }
        if($arr_post['tel'] && !preg_match("/^(0?(([1-9]\d)|([3-9]\d{2}))-?)?\d{7,8}$/",$arr_post['tel'])){
            $msg = app::get('b2c')->_('固定电话格式不正确');
            return false;
        }
        if(!$arr_post['mobile'] && !$arr_post['tel']){
            $msg = app::get('b2c')->_('手机号码和固定电话至少填写一个');
			return false;
        }

        if($arr_post['addr_id'] && !app::get('b2c')->model('member_addrs')->getList('addr_id',array('addr_id'=>$arr_post['addr_id'],'member_id'=>$member_id))){
            $msg = app::get('b2c')->_('更新的收货信息不存在');
			return false;
        }
        return  true;
    }

}

