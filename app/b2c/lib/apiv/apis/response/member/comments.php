<?php

class b2c_apiv_apis_response_member_comments{

	public $app;
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    private function check_accesstoken($accesstoken,$member_id){
        $_GET['sess_id'] = $accesstoken;
        kernel::single("base_session")->start();
        $userObject = kernel::single('b2c_user_object');
        $id = $userObject->get_member_id();
        if( empty($id) || $member_id != $id ){
            return false;
        }
        return true;
    }

    /**
     * 根据商品id获取商品评论列表
     * @param $goods_id
     * return $comment_detial
     */
    public function get_cat_comments($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        if(empty($params['goods_id'])){
            return $service->send_user_error('参数错误');
        }
        $params['object_type']='discuss';
        $member_comment=$this->app->model('member_comments');
        $count=$member_comment->count(array('type_id'=>intval($params['goods_id']),'object_type'=>$params['object_type']));
        $params['page_no'] = intval($params['page_no'])>0 ? $params['page_no'] : 1;
        $params['page_size'] = intval($params['page_size']) ? $params['page_size'] : 10;
        //获取总数量
        $page_total=ceil($count/$params['page_size']);
        if($params['page_no']>$page_total){
            $params['page_no']=$page_total;
        }

        $member_comments = kernel::single('b2c_message_disask');
        $setting=$member_comments->disask_setting($params['object_type']);
        if($setting['switch'] == 'on'){
            //获取详情
            $comment_detial=$member_comments->good_all_disask($params['goods_id'],$params['object_type'],$params['page_no'],null,$params['page_size']);
            foreach ($comment_detial['list'] as $key => $value) {
               foreach ($value as $k => $v) {
                    $date[$v['comment_id']]['comment_id'] = $v['comment_id'];
                    $date[$v['comment_id']]['comment'] = $v['comment'];
                    $date[$v['comment_id']]['comment_point'] = $v['goods_point'];
                    $date[$v['comment_id']]['member_name'] = $v['author'];
                    $date[$v['comment_id']]['time'] = $v['time'];
                    $date[$v['comment_id']]['display'] = $v['display'];
               }
            }
            $data['discuss']=$date;
            $data['count_num']= $comment_detial['discussCount'];
            return   $data;
        }else{
            return $service->send_user_error('后台关闭评论，请到后台->会员->消息设置 开启');
        }
    }

    /**
     * 获取评分参数
     * @return data 评分参数
     */
    public function get_point_params(){
         $goods_point = $this->app->model('comment_goods_type');
         $goodsPointData = $goods_point->getList('type_id,name,addon');
         foreach( $goodsPointData as $k=>$row ){
            $addon = unserialize($row['addon']);
            $return[$k]['type_id'] = $row['type_id'];
            $return[$k]['name'] = $row['name'];
            $return[$k]['is_total_point'] = $addon['is_total_point'];
         }
         return $return;
    }

     /**
     * 发表商品评论
     * @param $goods_id $member_id $comment_id
     * @param $comment_time $comment_context
     * @return boolean 成功还是失败
     */
    public function add_comments($params, &$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        $result = $this->_checkcomments($params,$msg);
        if( !$result ){
            return $service->send_user_error($msg);
        }
        if ($result['status']=='true') {
            $params['goods_point']=json_decode($params['goods_point'],true);
            $params = utils::_filter_input($params);//过滤xss攻击
            //请求数据验证合法有效性
            if(!$this->_checkInsertData($params,$msg)){
               return  $service->send_user_error($msg);
            }
            $pam_members= app::get('pam')->model('members');
            $params['object_type'] = 'discuss';
            $params['goods_id'] = $params['goods_id'];
            $params['product_id']= $params['product_id'];
            $params['order_id'] = $params['order_id'];
            $params['author_id'] = $params['member_id'] ? $params['member_id']:0;
            $params['author']= kernel::single('b2c_user_object')->get_member_name();
            $params['time']=time();
            $params['ip']=$_SERVER['REMOTE_ADDR'];
            $params['author'] = ($params['author'] ? $params['author'] : app::get('b2c')->_('佚名'));
            $params['lastreply'] = 0;
            if($params['hidden_name']){
                $params['hidden_name'] = "YES";
            }
            foreach ($this->get_point_params() as $key => $value) {
                $param[$value['type_id']]['point']=$params['goods_point'][$value['type_id']];
            }
            $params['goods_point']=$param;
            $params['display'] = ($this->app->getConf('comment.display')=='soon' ? 'true' : 'false');
            $objGoods = $this->app->model('goods');
            $objComment = kernel::single('b2c_message_disask');
            $setting=$objComment->disask_setting($params['object_type']);
            if($setting['switch'] == 'on'){
                $filter = array(
                        'order_id'=>intval($params['order_id']),
                        'object_type'=>"discuss",
                        'product_id' => intval($params['product_id']),
                        'author_id'=>intval($params['member_id']),
                        'for_comment_id' => '0'
                );
                $flag = app::get('b2c')->model('member_comments')->getList('comment_id',$filter);
                if($flag){
                    return array('status'=>'false','message'=>app::get('b2c')->_('商品已发表过评论'));
                }
                $objGoods->updateRank($params['goods_id'], $params['object_type'],1);
                if($comment_id = $objComment->send($params, $params['object_type'], $message)){
                    $comment_display = $this->app->getConf('comment.display');
                    if($comment_display == 'soon' && $item == 'discuss' && $params['author_id']){
                        $_is_add_point = app::get('b2c')->getConf('member_point');
                        if($_is_add_point){
                            $obj_member_point = $this->app->model('member_point');
                            $obj_member_point->change_point($aData['author_id'],$_is_add_point,$_msg,'comment_discuss',2,$params['goods_id'],$params['author_id'],'comment');
                        }
                    }

                    $setting_display = $comment_display ? $comment_display : 'reply';

                    if($setting_display == 'soon'){
                        $message = $this->app->getConf('comment.submit_display_notice.'.$params['object_type']);
                    }else{
                        $message = $this->app->getConf('comment.submit_hidden_notice.'.$params['object_type']);
                    }

                    $message = $message ? $message : app::get('b2c')->_('发表成功');

                    return array('status'=>'true','message'=>$message);
                }
                else{
                    return array('status'=>'false','message'=>app::get('b2c')->_('商品评论添加评论失败!'));
                }
            }else{
                return $service->send_user_error('后台关闭评论，请到后台->会员->消息设置 开启');
            }
        }else{
            return $result;
        }
        
    }

    /**
     * 根据商品id，查看是否能评论
     * @param int $gid
     * @param int $member_id
     * @return boolean 成功还是失败
     */
    public function is_discuss($params,&$service){
        if(!$this->check_accesstoken($params['accesstoken'],$params['member_id']) ){
            return $service->send_user_error('100001','accesstoken fail');
        }
        $result = $this->_checkcomments($params, $msg);
        if( !$result ){
            return $service->send_user_error($msg);
        }
        return $result;
    }

     /**
     * 验证新增商品评论的数据合理有效性
     * @param array param数据
     * @param string message
     * @return boolean 成功还是失败
     */
    private function _checkInsertData(&$param, &$msg=''){
        if (empty($param['goods_id'])){
            $msg = app::get('b2c')->_('商品id不能为空，必要参数！');
            return false;
        }
        if (empty($param['order_id'])){
            $msg = app::get('b2c')->_('订单id不能为空，必要参数！');
            return false;
        }
        if (empty($param['product_id'])){
            $msg = app::get('b2c')->_('货品id不能为空，必要参数！');
            return false;
        }

        if(empty($param['member_id'])){
            $msg = app::get('b2c')->_('用户id不能为空，必要参数！');
            return false;
        }
        return true;
    }

    private function _checkcomments($params, &$msg){
        if(empty($params['goods_id'])){
            $msg = '参数错误，商品ID必填';
            return false;
        }
        $params['object_type']='discuss';
        $objComment = kernel::single('b2c_message_disask');
        $setting=$objComment->disask_setting($params['object_type']);
        if($setting['switch'] != 'on'){
            $msg = '后台关闭评论，请到后台->会员->消息设置 开启';
            return false;
        }
        $sell_logs=$this->app->model('sell_logs');
        $filter=array(
            'goods_id'=>intval($params['goods_id']),
            'member_id'=>intval($params['member_id']),
            'order_id'=>intval($params['order_id']),
        );
        //判断是不是赠品
        $filter=array(
            'order_id'=>intval($params['order_id']),
            'product_id' => intval($params['product_id']),
            'goods_id'=>intval($params['goods_id']),
        );
        $goods=$this->app->model('goods');
        $order_items=$this->app->model('order_items');
        $goods_type=$goods->getRow('goods_type',array('goods_id'=>$params['goods_id']));
        if($goods_type['goods_type']=='gift'){
            return array('status'=>'false','message'=>app::get('b2c')->_('该商品是赠品,赠品不可以评论'));
        }
         //判断是不是赠品
        $item_type=$order_items->getRow('item_type',$filter);
        if($item_type['item_type']=='gift'){
            return array('status'=>'false','message'=>app::get('b2c')->_('该商品是赠品,赠品不可以评论'));
        }
        
        
        $row=$sell_logs->getRow('goods_id,member_id',$filter);
        if($row){
            $filter = array(
                'order_id'=>intval($params['order_id']),
                'object_type'=>"discuss",
                'product_id' => intval($params['product_id']),
                'author_id'=>intval($params['member_id']),
                'for_comment_id' => '0'
            );
            $flag = app::get('b2c')->model('member_comments')->getList('comment_id',$filter);
            if($flag){
                return array('status'=>'false','message'=>app::get('b2c')->_('商品已发表过评论'));
            }
            return array('status'=>'true','message'=>app::get('b2c')->_('商品可以评论')); 
        }else{
            return array('status'=>'false','message'=>app::get('b2c')->_('未购买此商品不能评论')); 
        }
    }

}
