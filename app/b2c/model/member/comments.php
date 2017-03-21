<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_member_comments extends dbeav_model{

 var $defaultOrder = array('time','DESC');
 var $has_one = array(
        'goods_point'=>'comment_goods_point',

    );

     var $has_many = array(
//        'product' => 'products:contrast',
    );
   function __construct(&$app){
        $this->app = $app;

        parent::__construct($app);

    }

   function get_type(){

          return $type;

   }

   function set_type($type){
      $this->type = $type;
   }
    function get_schema()
    {
        $schema = parent::get_schema();
        $params = $_GET;
        if($params['ctl']==='admin_member_discuss' || $params['ctl']==='admin_member_gask')
        {
            unset($schema['in_list'][5]);
            unset($schema['default_in_list'][2]);
            return $schema;
        }
        else
        {
            unset($schema['in_list'][0]);
            return $schema;
        }
    }
    function getList($cols='*', $filter=array(), $offset=0, $limit=-1, $orderby=null){
        if($this->type == 'msgtoadmin' || $this->falg == 'msgtoadmin'){
             $this->type = 'msg';
             $filter['to_id'] = 1;
         }
         if($this->type){
            $filter['object_type'] = $this->type;
        }
        if($filter['for_comment_id'] === 'all'){
             unset($filter['for_comment_id']);
        }
        else{
            if (isset($filter['for_comment_id']))
                $filter['for_comment_id'] = $filter['for_comment_id'] ? $filter['for_comment_id']:0;
        }
         $aData = parent::getList($cols, $filter, $offset, $limit, $orderby);
         return $aData;
  }

  function count($filter=array()){
       if($this->type == 'msgtoadmin'){
             $this->type = 'msg';
             $this->falg = 'msgtoadmin';
             $filter['to_id'] = 1;
         }
         if($this->type){
            $filter['object_type'] = $this->type;
        }
         if($filter['for_comment_id'] === 'all'){
             unset($filter['for_comment_id']);
         }
         else{
             $filter['for_comment_id'] = $filter['for_comment_id'] ? $filter['for_comment_id']:0;
            }
           return parent::count($filter);
  }
  /*设置管理员阅读状态*/
  function set_admin_readed($comment_id){
        $sdf = $this->dump($comment_id);
        $sdf['adm_read_status'] = 'true';
        $this->save($sdf);
  }

    function searchOptions(){
        $arr = parent::searchOptions();
        if($this->type ==='ask' || $this->type ==='discuss')
        {
            unset($arr['title']);
            return array_merge($arr,array(
                'name'=>app::get('b2c')->_('商品名称'),
                'bn'=>app::get('b2c')->_('商品编号'),
            ));
        }

        else
        {
            return $arr;
        }

    }

    public function fireEvent($action , &$object, $member_id=0)
    {
         $trigger = $this->app->model('trigger');

         return $trigger->object_fire_event($action, $object, $member_id, $this);
    }

    function _filter($filter,$tableAlias=null,$baseWhere=null){
        $objGoods = $this->app->model('goods');
        if($filter['name']){
            $goods_id = $objGoods->getList('goods_id',array('name|has'=>$filter['name']));
            if(is_array($goods_id)){
                   foreach($goods_id as $gk=>$gv){
                    $filter['type_id'][] = $gv['goods_id'];
                }
                if(!isset($filter['type_id'])){
                    $filter['comment_id'] = 0;
                }
            }
            unset($filter['name']);
        }
        if($filter['bn']){
            $goods_id = $objGoods->getList('goods_id',array('bn'=>$filter['bn']));
            if(is_array($goods_id)){
                   foreach($goods_id as $gk=>$gv){
                    $filter['type_id'][] = $gv['goods_id'];
                }
                if(!isset($filter['type_id'])){
                    $filter['comment_id'] = 0;
                }
            }
            unset($filter['bn']);
        }
        $filter = parent::_filter($filter);
        return $filter;
    }

    function getCommentByName(){

    }

    /**
     * @description 删除评论与咨询后触发短信等事件
     * @access public
     * @param array $data
     * @return boolean
     */
    public function pre_recycle($data) {
        $ret = $this->app->getConf('messenger.actions.comments-delete');
        if(!$ret) return true;
        $action = explode(',',$ret);
        $emailTmpl=''; $msgboxTmpl=''; $smsTmpl='';
        $systmpl = $this->app->model('member_systmpl');
        foreach($data as $key=>$value){
            if(!$value['author_id']) continue;

            $member = kernel::single('b2c_user_object')->get_pam_data('*',$value['author_id']);  

            //发邮件
            if(in_array('b2c_messenger_email',$action) && $member['email']){
                if(!$emailTmpl){
                    $emailTmpl = $systmpl->fetch('messenger:b2c_messenger_email/comments-delete',array());
                }
                $worker = 'b2c_tasks_sendemail';
                $params['acceptor'] = $member['email'];
                $params['body'] = $emailTmpl;
                $params['title'] = $this->app->_('删除评论与咨询');
            }

            //发站内信
            if(in_array('b2c_messenger_msgbox',$action)){
                if(!$msgboxTmpl){
                    $msgboxTmpl = $systmpl->fetch('messenger:b2c_messenger_msgbox/comments-delete',array());
                }
                $worker = 'b2c_tasks_sendmsg';
                $params['member_id'] = $value['author_id'];
                $params['data']['content'] = $msgboxTmpl;
                $params['data']['title'] = $this->app->_('删除评论与咨询');
                $params['name'] = $value['author'];
            }

            //发短信
            if(in_array('b2c_messenger_sms',$action) && $member['mobile']){
                if(!$smsTmpl) {
                   $smsTmpl = $systmpl->fetch('messenger:b2c_messenger_sms/comments-delete',array());
                }
                $worker = 'b2c_tasks_sendsms';
                $params['mobile_number'] = $member['mobile'];
                $params['data']['title'] = $this->app->_('删除评论与咨询');
                $params['data']['content'] = $smsTmpl;
            }

            if($worker){
                system_queue::instance()->publish($worker, $worker, $params);
            }
        }
        return true;
    }
}
