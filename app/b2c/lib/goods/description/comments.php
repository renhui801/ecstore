<?php
class b2c_goods_description_comments{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid,$item='ask',$limit){
        $objComment = kernel::single('b2c_message_disask');
        $aComment = $objComment->good_all_disask($gid,$item,1,null,$limit);
        $memberInfo = kernel::single('b2c_frontpage')->get_current_member();
        $params['member_id'] = $memberInfo['member_id'];
        if(!$params['member_id']){
            $aComment['setting']['login'] = 'nologin';
        }
        $validate_type = ($item == 'discuss') ? 'discussReply' : $item;
        // 评论回复/咨询/咨询回复的权限
        $aComment['setting']['power_status'] = kernel::single('b2c_message_disask')->toValidate($validate_type,$params,$message);
        $aComment['setting']['power_message'] = $message;

        if($item == 'ask'){
            $aComment['gask_type'] = $objComment->gask_type($gid);
        }else{
            $point_status = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
            if($point_status == 'on'){
                $objPoint = $this->app->model('comment_goods_point');
                $aComment['goods_point'] = $objPoint->get_single_point($gid);
                $aComment['total_point_nums'] = $objPoint->get_point_nums($gid);
                $aComment['_all_point'] = $objPoint->get_goods_point($gid);
            }
            $aComment['point_status'] = $point_status;
        }
	    return $aComment;
    }

}

