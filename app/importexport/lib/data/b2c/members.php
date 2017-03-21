<?php

class importexport_data_b2c_members {


    /**
     * 导出会员数据，修改数据库已有数据
     *
     * @params array $row 数据库中一条会员数据
     * @return array $row 修改过后的数据
     */
    public function get_content_row($row){
        $pam_account = kernel::single('b2c_user_object')->get_pam_data('*',$row['member_id']);
        $row['member_id'] = $pam_account['local'];
        $row['mobile'] = $pam_account['mobile'];
        $row['email'] = $pam_account['email'];
        $data[0] = $row;
        return $data;
    }//end function

}
