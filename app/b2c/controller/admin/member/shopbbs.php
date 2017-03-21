<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class b2c_ctl_admin_member_shopbbs extends desktop_controller{

    var $workground = 'b2c_ctl_admin_member';
    
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $member_comments = $this->app->model('member_comments');
        $member_comments->set_type('message');
        $this->finder('b2c_mdl_member_comments',array(
        'title'=>app::get('b2c')->_('留言列表'),
        'base_filter' =>array('for_comment_id' => 0),
        'use_buildin_recycle'=>true,
        'use_buildin_filter'=>true,
        'finder_aliasname'=>'shopbbs',
        'finder_cols'=>'author,title,comment,time',
      //  'actions'=>array(
      //      array('label'=>'留言设置','href'=>'index.php?app=b2c&ctl=admin_member_shopbbs&act=setting')
      //      )
        ));

    }

  function to_reply(){
   $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
   $comment_id = $_POST['comment_id'];
   $comment = $_POST['reply_content'];
   if($comment_id&&$comment){
      $member_comments = kernel::single('b2c_message_message');
      $display = $this->app->getConf('comment.display.discuss') ? $this->app->getConf('comment.display.discuss'): 'reply';
      if($display == "reply"){
         $_POST['display'] = "true";
      }
      if($member_comments->to_reply($_POST)){
         #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
         if($obj_operatorlogs = kernel::service('operatorlog.members')){
             if(method_exists($obj_operatorlogs,'reply_comment')){
                $sdf['comment'] = $_POST['reply_content'];
                $sdf['title'] = $_POST['title'];
                $sdf['object_type'] = 'message';
                $obj_operatorlogs->reply_comment($sdf);
             }
         }
         #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
         $this->end(true,app::get('b2c')->_('回复成功')); 
      }
      else{
         $this->end(false,app::get('b2c')->_('回复失败')); 
      }
   }
   else{
      $this->end(false,app::get('b2c')->_('内容不能为空'));
   }
  } 
  
  function delete_reply($msg_id){
   $this->begin("javascript:finderGroup["."'".$_GET["finder_id"]."'"."].refresh()");
   $member_msg = kernel::single('b2c_message_message');
   $resforlog = $this->app->model('member_comments')->getList('for_comment_id,object_type,title,comment',array('comment_id'=>$msg_id));
   if($member_msg->delete(array('comment_id' => $msg_id))){
      #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
      if($obj_operatorlogs = kernel::service('operatorlog.members')){
          if(method_exists($obj_operatorlogs,'delete_comment')){
              $obj_operatorlogs->delete_comment($resforlog[0]);
          }
      }
      #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
      $this->end(true,app::get('b2c')->_('删除成功')); 
   }
   else{
      $this->end(false,app::get('b2c')->_('删除失败'));
   }
}
 
 function setting(){
     $this->pagedata['setting']['open'] = $this->app->getConf('system.message.open');
     $this->pagedata['setting']['power'] = $this->app->getConf('system.message.power')?$this->app->getConf('system.message.power'):'member';
     echo $this->fetch('admin/member/setting.html');
 }
 
 function to_setting(){
    $this->begin();
    if($_POST){
         $this->app->setConf('system.message.open',$_POST['open']);
         $this->app->setConf('system.message.power',$_POST['power']);
     }
    $this->end('success',app::get('b2c')->_('设置成功'));
    
 }
    

}
