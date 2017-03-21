<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 * b2c member interactor with center
 * shopex team
 * dev@shopex.cn
 */
class b2c_apiv_apis_20_member
{

    //初始化会员信息
    public function init($params, &$service){
        $membersModel = app::get('b2c')->model('members');
        $userPassport = kernel::single('b2c_user_passport');

        //默认分页码为1,分页大小为20
        $params['page_no'] = is_int($params['page_no']) ? $params['page_no'] : 1;
        $params['page_size'] = is_int($params['page_size']) ? $params['page_size'] : 20;
        $page_no = intval($params['page_no']) - 1;
        $limit  = intval($params['page_size']);
        $offset = $page_no * $limit;

        //返回总数
        $rows = $membersModel->count();
        $data['item_total'] = $rows;

        $membersData = $membersModel->getList('*',array(),$offset,$limit);
        $data['list'] = array();
        foreach( (array)$membersData as $k=>$row ){
            //获取到用户名，手机号, 邮箱
            $pam_colunms = $userPassport->userObject->get_pam_data('*',$row['member_id']);

            //获取到注册项数据
            $attrData = $userPassport->get_signup_attr($row['member_id']);
            $attr = array();
            foreach( (array)$attrData  as $attr_k=>$attr_colunm){
                $attr[$attr_k]['attr_name'] = $attr_colunm['attr_name'];
                $attr[$attr_k]['attr_column'] = $attr_colunm['attr_column'];
                $attr[$attr_k]['attr_value'] = $attr_colunm['attr_value'];
            }
            $data['list'][$k]['member_id'] = intval($row['member_id']);
            $data['list'][$k]['member_lv_id'] = intval($row['member_lv_id']);
            $data['list'][$k]['login_name'] = $pam_colunms['local'];
            $data['list'][$k]['mobile'] = $pam_colunms['mobile'];
            $data['list'][$k]['email'] = $pam_colunms['email'];
            $data['list'][$k]['reg_ip'] = $row['reg_ip'];
            $data['list'][$k]['regtime'] = $row['regtime'];
            $data['list'][$k]['attr'] = $attr;
            $data['list'][$k]['last_modify'] = '';
        }
       
        return $data;
    }

    /**
     *获取到会员等级列表
     */
    public function get_member_lv_list($params,&$service){
        $memberLvModel = app::get('b2c')->model('member_lv');
        $memberLvData = $memberLvModel->getList('*');
        $data = array();
        foreach( (array)$memberLvData as $k=>$row){
            $data[$k]['member_lv_id'] = intval($row['member_lv_id']);
            $data[$k]['name']         = $row['name'];
            $data[$k]['default_lv']   = ($row['default_lv'] == '1') ? 'true' : 'false';
        }
        return $data;
    }
}
