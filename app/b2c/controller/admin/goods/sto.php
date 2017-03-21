<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_ctl_admin_goods_sto extends desktop_controller{
    /*
        %1 - id
        %2 - title
        $s - string
        $d - number
    */
    var $workground = 'b2c_ctl_admin_goods';

    function index(){
        $this->finder('b2c_mdl_goods_sto',array(
            'title'=>app::get('b2c')->_('缺货信息列表'),
            'actions'=>array(array('label'=>app::get('b2c')->_('发送到货通知'),'submit'=>'index.php?app=b2c&ctl=admin_goods_sto&act=send')),
            'use_buildin_recycle'=>true,
            ));
    }

     function send(){
        $this->begin('index.php?app=b2c&ctl=admin_goods_sto&act=index');
		/** 判断是否能够发送 **/
		$conf_goods_notify = $this->app->getConf('messenger.actions.goods-notify');
		if (!$conf_goods_notify){
			$this->end(false,app::get('b2c')->_('必须在邮件短信管理里面勾选-手机短信，站内消息或者电子邮件其中一项才可以！'));
		}
		$arr_conf_goods_notify = explode(',',$conf_goods_notify);

        $systmpl = $this->app->model('member_systmpl');
        $obj_product = app::get('b2c')->model('products');
        $member_goods = app::get('b2c')->model('member_goods');
         if($_POST['isSelectedAll'] == '_ALL_'){
            $aGnotify = array();
            $aData = $member_goods->getList('gnotify_id');
            foreach((array)$aData as $key => $val){
                $aGnotify[] = $val['gnotify_id'];
            }
        }
        else{
             $aGnotify = $_POST['gnotify_id'];
        }
        foreach( $aGnotify as $gnid){
            $data = $member_goods->dump($gnid);
            if($data['member_id']){
                $member_obj = $this->app->model('members');
                $login_name = kernel::single('b2c_user_object')->get_member_name(null,$data['member_id']); 
            }
            else{
                $login_name = app::get('b2c')->_("顾客");
            }
            $goods = $obj_product->dump($data['product_id']);
            $obj['goods_name'] = $goods['name'];
            $obj['goods_id'] = $goods['goods_id'];
            $obj['username'] = $login_name;
            $obj['url'] = app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$data['product_id']));
			if (in_array('b2c_messenger_email',$arr_conf_goods_notify)){
				$content = $systmpl->fetch('messenger:b2c_messenger_email/goods-notify',$obj);
				$params = array(
                    'acceptor'=>$data['email'],
                    'body' =>$content,
                    'title' =>app::get('b2c')->_('到货通知'),
                    'product_id' => $data['product_id'],
                    'gnotify_id' => $gnid,
                );
				if(!system_queue::instance()->publish('b2c_tasks_sendemail', 'b2c_tasks_sendemail', $params)){
					$this->end(false,app::get('b2c')->_('操作失败！'));
				}
			}
            if($data['member_id']){
				if (in_array('b2c_messenger_msgbox',$arr_conf_goods_notify)){
					$aTmp['content'] =  $systmpl->fetch('messenger:b2c_messenger_msgbox/goods-notify',$obj);
					$aTmp['title'] =app::get('b2c')->_("商品到货通知");
					$params = array(
                        'member_id'=>$data['member_id'],
                        'data' =>$aTmp,
                        'name' => $login_name,
                        'gnotify_id' => $gnid,
                    );
					if(!system_queue::instance()->publish('b2c_tasks_sendmsg', 'b2c_tasks_sendmsg', $params)){
						$this->end(false,app::get('b2c')->_('操作失败！'));
					}
				}
            }
            //发到货通知到手机
            if(in_array('b2c_messenger_sms',$arr_conf_goods_notify)&&$data['cellphone']){
                $aSms['content'] =  $systmpl->fetch('messenger:b2c_messenger_sms/goods-notify',$obj);
                $aSms['title'] =app::get('b2c')->_("商品到货通知");
                $aSms['sendType'] = 'fan-out';
                $params = array(
                    'mobile_number'=>$data['cellphone'],
                    'data' =>$aSms,
                );
                if(!system_queue::instance()->publish('b2c_tasks_sendsms', 'b2c_tasks_sendsms', $params)) {
                    $this->end(false,app::get('b2c')->_('操作失败！'));
                }
            }
        }
        $this->end(ture,app::get('b2c')->_('操作成功！'));
    }
}
