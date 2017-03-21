<?php
/*
 * 订单导入导出数据处理类
 * */
class importexport_data_b2c_orders {


    /**
     * 导出订单数据，修改数据库已有数据
     *
     * @params array $row 数据库中一条订单数据
     * @return array $row 修改过后的数据
     */
    public function get_content_row($row){
        $login_name = app::get('pam')->model('members')->get_operactor_name($row['member_id']);
        $row['member_id'] = $login_name;
        if($row['payment'] == '-1'){
            $row['payment'] = app::get('importexport')->_('货到付款');
        }
        $data[0] = $row;
        return $data;
    }//end function
}
