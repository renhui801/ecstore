<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2012 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

#会员
class operatorlog_members{

    function __construct(){
        $this->objlog = kernel::single('operatorlog_service_desktop_controller');
        $this->delimiter = kernel::single('operatorlog_service_desktop_controller')->get_delimiter();
    }


    function detail_edit_log($newdata,$olddata){
        $modify_flag = 0;
        $data = array();
        foreach($newdata as $key=>$val){
            if($newdata[$key] != $olddata[$key]){
                $data['new'][$key] = $val;
                $data['old'][$key] = $olddata[$key];
                $modify_flag++;
            }
        }
        if($modify_flag>0){
            $memo  = "serialize".$this->delimiter."编辑会员ID {$_POST['pam_account']['account_id']}".$this->delimiter.serialize($data);
            $this->objlog->logs('member', '会员编辑', $memo);
        }
    }



    function detail_advance_log($newdata,$olddata){
        $modify_flag = 0;
        $data = array();
        foreach($newdata as $key=>$val){
            if($newdata[$key] != $olddata[$key]){
                $data['new'][$key] = $val;
                $data['old'][$key] = $olddata[$key];
                $modify_flag++;
            }
        }
        if($modify_flag>0){
            $memo  = "serialize".$this->delimiter."编辑会员ID {$newdata['pam_account']['account_id']}".$this->delimiter.serialize($data);
            $this->objlog->logs('member', '会员编辑(预存款)', $memo);
        }
    }


    function detail_experience_log($newdata,$olddata){
        $modify_flag = 0;
        $data = array();
        foreach($newdata as $key=>$val){
            if($newdata[$key] != $olddata[$key]){
                $data['new'][$key] = $val;
                $data['old'][$key] = $olddata[$key];
                $modify_flag++;
            }
        }
        if($modify_flag>0){
            $memo  = "serialize".$this->delimiter."编辑会员ID {$newdata['pam_account']['account_id']}".$this->delimiter.serialize($data);
            $this->objlog->logs('member', '会员编辑(经验值)', $memo);
        }
    }


    function detail_point_log($newdata,$olddata){
        $modify_flag = 0;
        $data = array();
        foreach($newdata as $key=>$val){
            if($newdata[$key] != $olddata[$key]){
                $data['new'][$key] = $val;
                $data['old'][$key] = $olddata[$key];
                $modify_flag++;
            }
        }
        if($modify_flag>0){
            $memo  = "serialize".$this->delimiter."编辑会员ID {$newdata['pam_account']['account_id']}".$this->delimiter.serialize($data);
            $this->objlog->logs('member', '会员编辑(积分)', $memo);
        }
    }



    function detail_remark_log($newdata,$olddata){
        $modify_flag = 0;
        $data = array();
        foreach($newdata as $key=>$val){
            if($newdata[$key] != $olddata[$key]){
                $data['new'][$key] = $val;
                $data['old'][$key] = $olddata[$key];
                $modify_flag++;
            }
        }
        if($modify_flag>0){
            $memo  = "serialize".$this->delimiter."编辑会员ID {$newdata['pam_account']['account_id']}".$this->delimiter.serialize($data);
            $this->objlog->logs('member', '会员编辑(备注)', $memo);
        }
    }


    function member_lv_log($newdata,$olddata){
        if(empty($_POST['member_lv_id'])){
            $this->objlog->logs('goods', '添加会员等级', '添加会员等级 '.$_POST['name']);
        }else{
            $modify_flag = 0;
            $data = array();
            foreach($newdata as $key=>$val){
                if($newdata[$key] != $olddata[$key]){
                    $data['new'][$key] = $val;
                    $data['old'][$key] = $olddata[$key];
                    $modify_flag++;
                }
            }
            if($modify_flag>0){
                $memo  = "serialize".$this->delimiter."编辑会员等级ID {$_POST['member_lv_id']}".$this->delimiter.serialize($data);
                $this->objlog->logs('goods', '编辑会员等级', $memo);
            }
        }
    }


    public function delete_comment($commentInfo){
        $arr = array('discuss'=>'评论','ask'=>'咨询','message'=>'留言','msg'=>'站内信');
        if($commentInfo['for_comment_id']<1){
            $obj_type = '删除'.$arr[$commentInfo['object_type']];
            $memo = '删除'.$arr[$commentInfo['object_type']].' '.$commentInfo['title'].': '.$commentInfo['comment'];
        }else{
            $obj_type = '删除'.$arr[$commentInfo['object_type']].'回复';
            $memo = '删除'.$arr[$commentInfo['object_type']].'回复 '.$commentInfo['title'].': '.$commentInfo['comment'];
        }
        $this->objlog->logs('member', $obj_type, $memo);
    }

    public function reply_comment($commentInfo){//p($commentInfo);
        $arr = array('discuss'=>'评论','ask'=>'咨询','message'=>'留言','msg'=>'站内信');
        $obj_type = '回复'.$arr[$commentInfo['object_type']];
        $memo = '回复'.$arr[$commentInfo['object_type']].' '.$commentInfo['title'].': '.$commentInfo['comment'];
        $this->objlog->logs('member', $obj_type, $memo);
    }


}//End Class
