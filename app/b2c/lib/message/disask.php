<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_message_disask extends b2c_message_comment{

    function __construct(&$app){

        $this->app = $app;
        parent::__construct($app);
    }

    #插入评论/咨询
    function send($aData,$item){
        $sdf['for_comment_id'] = $aData['for_comment_id']?$aData['for_comment_id']:0;
        if($sdf['for_comment_id']){
            $aRes = $this->dump($sdf['for_comment_id']);
            unset($aRes['goods_point']);
            $aRes['lastreply'] = time();
            $aRes['reply_name'] = $aData['author'];
            $this->save($aRes);
        }
        $sdf['type_id'] = $aData['goods_id'];
        $sdf['object_type'] = $item;
        $sdf['author_id'] = $aData['author_id'];
        $sdf['order_id'] = $aData['order_id'];
        $sdf['product_id'] = $aData['product_id'];
        $sdf['author'] = $aData['author'];
        $sdf['to_id'] = $aData['to_id'];
        $sdf['contact'] = htmlspecialchars($aData['contact']);
        $sdf['title'] = htmlspecialchars($aData['title']);
        $sdf['comment'] = htmlspecialchars($aData['comment']);
        $sdf['time'] = $aData['time'];
        $sdf['lastreply'] = $aData['lastreply'];
        $sdf['ip'] = $aData['ip'];
        $sdf['display'] = $aData['display'];
        if($aData['hidden_name']){
            $addon['hidden_name'] = "YES";
        }
        if($aData['gask_type'] && $item == 'ask'){
            $sdf['gask_type'] = $aData['gask_type'];
        }
        $sdf['addon'] = serialize($addon);
        if($this->save($sdf)){
            if($item == 'discuss' && $aData['goods_point']){
                $goods_point = $this->app->model('comment_goods_point');
                $_pointsdf['comment_id'] = $sdf['comment_id'];
                foreach($aData['goods_point'] as $key=>$val){
                    if($aData['display'] == 'true')
                        $_pointsdf['display'] = 'true';
                    else
                        $_pointsdf['display'] = 'false';
                    #$_pointsdf['addon'] = serialize($_pointsdf_addon);
                    $_pointsdf['goods_id'] = $aData['goods_id'];
                    $_pointsdf['goods_point'] = (float)$val['point'];
                    if($_pointsdf['goods_point']<1) $_pointsdf['goods_point']=5;
                    ($_pointsdf['goods_point']<=5) or $_pointsdf['goods_point']=5;
                    $_pointsdf['member_id'] = $aData['author_id'];
                    $_pointsdf['type_id'] = $key;
                    $goods_point->save($_pointsdf);
                    unset($_pointsdf['point_id']);
                }
            }
            return $sdf['comment_id'];
        }
        else{
            return false;
        }
    }


    //读取商品评论回复列表
    function getCommentsReply($aId, $display=false){
        if($display)
        {
            $aData = $this->getList('*',array('for_comment_id' => $aId,'display' => 'true'));
        }
        return $aData;
    }

    function get_member_disask($member_id=null,$page=1,$object_type='ask',$limit){
        if(!$member_id) return null;
        $list_listnum = $limit ? $limit : intval($this->app->getConf('comment.index.listnum'));
        if($list_listnum == 0 || $list_listnum == '') return ;
        $this->objComment->type = $object_type;
        $count = $this->count(array('for_comment_id' => 0,'author_id' => $member_id,'display' => 'true'));
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $params['data'] = $this->getList('*',array('for_comment_id' => 0,'author_id' => $member_id,'display' => 'true'),$start,$list_listnum);
        foreach($params['data'] as $key=>$v){
            $params['data'][$key]['items'] = $this->get_reply($v['comment_id']);
        }
        $params['page'] = $maxPage;
        return $params;
    }

    //咨询评论回复记录数
    function calc_unread_disask($member_id){
        $this->objComment->type = array('ask','discuss');
        $aData = $this->getList('comment_id',array('author_id' => $member_id));
        $i = 0;
        foreach((array)$aData as $v){
            $row = $this->getList('comment_id',array('for_comment_id' => $v['comment_id']));
            if($row){
                $i++;
            }
        }
        return $i;
    }

    //获取商品咨询和评论包括回复
    function good_all_disask($gid=null,$item,$page=1,$type_id=null,$limit){
        if(!$gid) return;
        $setting = $this->disask_setting($item);
        if($setting['switch'] == 'on'){
            $commentList = $this->getGoodsIndexComments($gid,$item,$page,$type_id,$limit);
            $aComment['list'][$item] = $commentList['data'];
            $aComment['page']['start'] = $commentList['start'];
            $aComment['page']['end'] = $commentList['end'];
            $aComment[$item.'Count'] = $commentList['total'];
            $aComment[$item.'current'] = $commentList['current_page'];
            $aComment[$item.'totalpage'] = $commentList['page'];
            for($i=0;$i<$commentList['page'];$i++){
                $aComment[$item.'Page'][] = $i;
            }
            $aId = array();
            if ($commentList['total']){
                foreach($aComment['list'][$item] as $rows){
                    $aId[] = $rows['comment_id'];
                }
                if(count($aId)) $aReply = (array)$this->getCommentsReply($aId, true);
                reset($aComment['list'][$item]);
                foreach($aComment['list'][$item] as $key => $rows){
                    foreach($aReply as $rkey => $rrows){
                        if($rows['comment_id'] == $rrows['for_comment_id']){
                            $aComment['list'][$item][$key]['items'][] = $aReply[$rkey];
                        }
                    }
                    reset($aReply);
                }
            }else{
                $setting['null_notice'] = $this->app->getConf('comment.null_notice.'.$item);
            }
        }
        $aComment['setting'] = $setting;
        return $aComment;
    }



    function getGoodsIndexComments($gid,$item,$page=1,$type_id=null,$limit){
        if($limit){
            $list_listnum = $limit;
        }else{
            $list_listnum = intval($this->app->getConf('comment.index.listnum'));
        }
        $this->objComment->type = $item;
        $filter['for_comment_id'] = 0;
        $filter['type_id'] = $gid;
        $filter['display'] = 'true';
        if($type_id) $filter['gask_type'] = $type_id;
        $count = $this->count($filter);
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $aData = $this->getList('*',$filter,$start,$list_listnum);
        $data = array();
        $point_status = app::get('b2c')->getConf('goods.point.status') ? app::get('b2c')->getConf('goods.point.status'): 'on';
        $goods_point = $this->app->model('comment_goods_point');
        foreach((array)$aData as $key=>$val){
            if($val['object_type'] == 'discuss' && $point_status == 'on'){
                $row = $goods_point->get_comment_point($val['comment_id']);
                $val['goods_point'] = $row;
            }
            $data[] = $val;
        }
        $result['start'] = $start+1;
        $result['end'] = $start+$list_listnum;
        $result['total'] = $count;
        $result['data'] = $data;
        $result['page'] = $maxPage;
        $result['current_page'] = $page;
        return $result;
    }

    function getGoodsCommentList($gid,$item,$page=1){
        $list_listnum = 10;
        $this->objComment->type = $item;
        $count = $this->count(array('for_comment_id' => 0,'type_id'=>$gid,'display'=>'true'));
        $maxPage = ceil($count / $list_listnum);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * $list_listnum;
        $start = $start<0 ? 0 : $start;
        $data = $this->getList('*',array('for_comment_id' => 0,'type_id'=>$gid,'display'=>'true'),$start,$list_listnum);
        $result['total'] = $count;
        $result['page'] = $maxPage;
        $result['data'] = $data;
        return $result;
    }

    //咨询项目并且统计每个项目的咨询数量
    public function gask_type($gid){
      $gask_type = unserialize($this->app->getConf('gask_type'));
        if($gask_type){
            foreach($gask_type as $key => $val){
                $gask_type[$key]['total'] = $this->get_ask_total($gid,$val['type_id'],'ask');
            }
        }
      return $gask_type;
    }

    //咨询类型总数 企业版
    function get_ask_total($gid,$type_id,$item){
        $this->objComment->type = $item;
        $count = $this->count(array('for_comment_id' => 0,'type_id'=>$gid,'display'=>'true','gask_type'=>$type_id));
        return $count;
    }

    /*
     * 咨询评论发表权限验证
     * */
    public function toValidate($item,$params, &$message){
        if($this->app->getConf('comment.switch.'.$item) == 'off'){
            return false;
        }

        //检查咨询评论是否开启
        if($item == 'askReply' || $item == 'discussReply'){
            if($this->app->getConf('comment.switch_reply') == 'off'){
                $message = app::get('b2c')->_('没有开启回复功能!');
                return false;
            }
        }

        if(!isset($params['member_id'])){
            $member_id = kernel::single('b2c_user_object')->get_member_id();
            $params['member_id'] = $member_id;
        }

        if(isset($params['member_id']) && $params['member_id']){
            if($item == 'discuss'){
                if(empty($params['order_id']) || empty($params['product_id'])){
                    $message = app::get('b2c')->_('参数错误');
                    return false;
                }

                //是否购买过
                if(app::get('b2c')->model('orders')->getList('order_id',array('order_id'=>$params['order_id'],'member_id'=>$params['member_id'])) ){
                    if( !app::get('b2c')->model('order_items')->getList('order_id',array('product_id'=>$params['product_id'],'order_id'=>$params['order_id'],'item_type|noequal'=>'gift')) ){
                        $message = app::get('b2c')->_('未购买此商品不能评论');
                        return false;
                    }
                }else{
                    $message = app::get('b2c')->_('参数错误');//无效的订单号
                    return false;
                }

                $filter = array(
                    'order_id'=>$params['order_id'],
                    'object_type'=>"discuss",
                    'product_id' => $params['product_id'],
                    'author_id'=>$params['member_id'],
                    'for_comment_id' => '0'
                );
                $flag = app::get('b2c')->model('member_comments')->getList('comment_id',$filter);
                if($flag){
                    $message = app::get('b2c')->_('商品已发表过评论');
                    return false;
                }
            }
        }else{
            if(($item == 'ask' && $this->app->getConf('comment.power.ask') != 'null') || $item=='discussReply'){
                if($item == 'ask'){
                    $message = app::get('b2c')->_('请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login', 'arg' =>'')).'">登录</a>后再咨询,如果您不是会员请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c','ctl' => 'site_passport', 'act' => 'signup', 'arg' =>'')).'">注册</a>!');
                }else{
                    $message = app::get('b2c')->_('请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c', 'ctl' => 'site_passport', 'act' => 'login', 'arg' =>'')).'">登录</a>后再回复,如果您不是会员请<a href="'.app::get('site')->router()->gen_url(array('app' => 'b2c','ctl' => 'site_passport', 'act' => 'signup', 'arg' =>'')).'">注册</a>!');
                }
                return false;
            }
        }
        return true;
    }

    #获取商店商品评论 挂件使用
    function getTopComment($limit=10,$item='discuss'){
        $this->objComment->type = $item;
        $goods = $this->app->model('goods');
        $row = $this->getList('*',array('for_comment_id' => 0,'display'=>'true'),0,$limit);
        $data = array();
        foreach($row as $val){
            $gids[] = $val['type_id'];
            $data[$val['type_id']] = $val;
        }
        if($gids){
            $row_ = $goods->getList('goods_id,name,thumbnail_pic,udfimg,image_default_id',array('goods_id' => $gids));
            foreach($row_ as $v){
                $data[$v['goods_id']]['name'] = $v['name'];
                $data[$v['goods_id']]['thumbnail_pic'] = $v['thumbnail_pic'];
                $data[$v['goods_id']]['udfimg'] = $v['udfimg'];
                $data[$v['goods_id']]['image_default_id'] = $v['image_default_id'];
            }
        }
        return $data;
    }

    //获取咨询和评论的配置信息 前台使用
    public function disask_setting($item){
        $setting = $this->get_basic_setting();
        if(!$item){
            return $setting;
        }
        $setting['switch'] = $this->app->getConf('comment.switch.'.$item);
        if($setting['switch'] == 'on'){
            $setting['submit_comment_notice'] = $this->app->getConf('comment.submit_comment_notice.'.$item);
            if($item == 'discuss'){
                $setting['goods_discuss_notice'] = $this->app->getConf('comment.goods_discuss_notice');
                $goods_point_status = app::get('b2c')->getConf('goods.point.status');
                $setting['goods_point_status']= $goods_point_status ? $goods_point_status : 'on';
            }
            if($setting['display'] == 'soon'){
                $setting['submit_notice'] = $this->app->getConf('comment.submit_display_notice.'.$item);
            }else{
                $setting['submit_notice'] = $this->app->getConf('comment.submit_hidden_notice.'.$item);
            }
        }
        return $setting;
    }

    #获取设置 后台使用
    function get_setting($item){
        $aOut['switch'][$item] = $this->app->getConf('comment.switch.'.$item);
        $aOut['power'][$item] = $this->app->getConf('comment.power.'.$item);
        $aOut['null_notice'][$item] = $this->app->getConf('comment.null_notice.'.$item);
        $aOut['submit_display_notice'][$item] = $this->app->getConf('comment.submit_display_notice.'.$item);
        $aOut['submit_hidden_notice'][$item] = $this->app->getConf('comment.submit_hidden_notice.'.$item);
        $aOut['submit_comment_notice'][$item] = $this->app->getConf('comment.submit_comment_notice.'.$item);
        $aOut['goods_discuss_notice'] = $this->app->getConf('comment.goods_discuss_notice');
        return $aOut;
    }

    #设置
    function to_setting($item,$aData){
        $this->app->setConf('comment.switch.'.$item, $aData['switch'][$item]); //是否开启
        $this->app->setConf('comment.power.'.$item, $aData['power'][$item]); //发表权限 member 会员 null 所有
        $this->app->setConf('comment.null_notice.'.$item, $aData['null_notice'][$item]);//无咨询时缺省文字
        $this->app->setConf('comment.submit_display_notice.'.$item, $aData['submit_display_notice'][$item]); //发表成功提示
        $this->app->setConf('comment.submit_hidden_notice.'.$item, $aData['submit_hidden_notice'][$item]); //等待审核提示
        $this->app->setConf('comment.submit_comment_notice.'.$item, $aData['submit_comment_notice'][$item]); //用户填写咨询时所见提示
        if($item == 'discuss')$this->app->setConf('comment.goods_discuss_notice', $aData['goods_discuss_notice']);
    }

    #获取基本设置
    function get_basic_setting(){
        $aOut['switch_reply'] = $this->app->getConf('comment.switch_reply') ? $this->app->getConf('comment.switch_reply'):'off';
        $aOut['display_lv'] = $this->app->getConf('comment.display_lv') ?$this->app->getConf('comment.display_lv'):'off' ;
        $aOut['display'] = $this->app->getConf('comment.display') ? $this->app->getConf('comment.display'): 'reply';
        $aOut['index'] = intval($this->app->getConf('comment.index.listnum'));
        $aOut['verifyCode'] = $this->app->getConf('comment.verifyCode')? $this->app->getConf('comment.verifyCode'):'on';
        return $aOut;
    }

    /*
     * 设置基本设置
     * */
    public function save_basic_setting($aData){
        if($aData['indexnum'] <=0) $aData['indexnum'] = 5;
        $this->app->setConf('comment.display', $aData['display']);//审核设置  soon立即显示 reply审核
        $this->app->setConf('comment.display_lv', $aData['display_lv']);
        $this->app->setConf('comment.switch_reply', $aData['switch_reply']);
        $this->app->setConf('comment.index.listnum', $aData['indexnum']);
        $this->app->setConf('comment.verifyCode', $aData['verifyCode']);
    }
}
