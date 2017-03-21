<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class b2c_finder_members{
	var $detail_basic;
	var $detail_edit;
	var $detail_advance;
	var $detail_experience;
	var $detail_point;
	var $detail_order;
	var $detail_msg;
	var $detail_remark;
	var $column_editbutton;
    var $pagelimit = 10;

    public function __construct($app)
    {
        $this->app = $app;
        $this->controller = app::get('b2c')->controller('admin_member');

		$this->detail_basic = app::get('b2c')->_('会员信息');
		$this->detail_edit = app::get('b2c')->_('编辑会员');
		$this->detail_advance = app::get('b2c')->_('预存款');
		$this->detail_experience = app::get('b2c')->_('经验值');
		$this->detail_point = app::get('b2c')->_('积分');
		$this->detail_order = app::get('b2c')->_('订单');
		$this->detail_msg = app::get('b2c')->_('站内信');
		$this->detail_remark = app::get('b2c')->_('会员备注');
		$this->column_editbutton = app::get('b2c')->_('操作');
		$this->column_uname = app::get('b2c')->_('用户名');
		$this->column_email = app::get('b2c')->_('EMAIL');
		$this->column_mobile = app::get('b2c')->_('手机');
        $this->userObject = kernel::single('b2c_user_object');
    }

    function detail_basic($member_id){
        $app = app::get('b2c');
        $userObject = kernel::single('b2c_user_object');

        $member_model = $this->app->model('members');
        $a_mem = $member_model->dump($member_id);
        $accountData = $userObject->get_members_data(array('account'=>'login_account'),$member_id);
        $a_mem['contact']['name'] = $accountData['account']['local'];
        $a_mem['contact']['email'] = $accountData['account']['email'];
        $a_mem['contact']['phone']['mobile'] = $accountData['account']['mobile'];

		$obj_extend_point = kernel::service('b2c.member_extend_point_info');
		if ($obj_extend_point)
		{
			// 当前会员拥有的积分
			$obj_extend_point->get_real_point($member_id, $a_mem['score']['total']);
			// 当前会员实际可以使用的积分
			$obj_extend_point->get_usage_point($member_id, $a_mem['score']['usage']);
		}

        $userPassport = kernel::single('b2c_user_passport');
        $render = $app->render();
        $render->pagedata['attr'] = $userPassport->get_signup_attr($member_id);
        $render->pagedata['mem'] = $a_mem;
        $render->pagedata['member_id'] = $member_id;
        // 判断是否使用了推广服务
        $is_bklinks = 'false';
        $obj_input_helpers = kernel::servicelist("html_input");
        if (isset($obj_input_helpers) && $obj_input_helpers)
        {
            foreach ($obj_input_helpers as $obj_bdlink_input_helper)
            {
                if (get_class($obj_bdlink_input_helper) == 'bdlink_input_helper')
                {
                    $is_bklinks = 'true';
                }
            }
        }
        $render->pagedata['is_bklinks'] = $is_bklinks;
        return $render->fetch('admin/member/detail.html');
    }


    function detail_edit($member_id){
        $app = app::get('b2c');
        $member_model = $app->model('members');
        $userPassport = kernel::single('b2c_user_passport');
        $userObject = kernel::single('b2c_user_object');

        if($_POST){
            $_POST['member_id'] = $member_id;
            $saveData['b2c_members'] = $_POST;
            unset($saveData['b2c_members']['pam_members']);
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                $olddata = app::get('b2c')->model('members')->dump($member_id);
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            if( $member_model->save($saveData['b2c_members']) ){
                #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                if($obj_operatorlogs = kernel::service('operatorlog.members')){
                    if(method_exists($obj_operatorlogs,'detail_edit_log')){
                        $newdata = app::get('b2c')->model('members')->dump($member_id);
                        $obj_operatorlogs->detail_edit_log($newdata['contact'],$olddata['contact']);
                    }
                }
                #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                if( $member_rpc_object = kernel::service("b2c_member_rpc_sync") ) {
                    $member_rpc_object->modifyActive($member_id);
                }     
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{success:"'.$msg.'",_:null}';
                exit;
            }else{
                $msg = app::get('b2c')->_('保存失败');
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
            }
        }

        $membersData = $userObject->get_members_data(array('account'=>'*','members'=>'*'),$member_id,false);
        $member_lv=$app->model("member_lv");
        foreach($member_lv->getMLevel() as $row){
            $options[$row['member_lv_id']] = $row['name'];
        }
        $membersData['lv']['options'] = is_array($options) ? $options : array(app::get('b2c')->_('请添加会员等级')) ;
        $membersData['lv']['value'] = $membersData['members']['member_lv_id'];

        $render = $app->render();
        $render->pagedata['mem'] = $membersData;
        $render->pagedata['attr'] = $userPassport->get_signup_attr($member_id);
        $render->pagedata['member_id'] = $member_id;
        return $render->fetch('admin/member/edit.html');
    }


    function detail_advance($member_id=null){
        if(!$member_id) return null;
        $nPage = $_GET['detail_advance'] ? $_GET['detail_advance'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $app = app::get('b2c');
        $member = $app->model('members');
        $mem_adv =  $app->model('member_advance');
        if($_POST){
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                $olddata = app::get('b2c')->model('members')->dump($member_id);
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            if(!$mem_adv->adj_amount($member_id,$_POST,$msg,false)){
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';
                    exit;
            }
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                if(method_exists($obj_operatorlogs,'detail_advance_log')){
                    $newdata = app::get('b2c')->model('members')->dump($member_id);
                    $obj_operatorlogs->detail_advance_log($newdata,$olddata);
                }
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        }

       // $items_adv = $mem_adv->get_list_bymemId($member_id );
        $data = $member->dump($member_id,'*',array('advance/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $items_adv = $data['advance']['event'];
        //后台会员列表，详细栏的预存款,支付方式改为显示中文-@lujy-start
        foreach($items_adv as $key=>$item){
            if(!empty($item['paymethod'])){
               $oPayName = app::get('ectools')->model('payment_cfgs');
               $items_adv[$key]['paymethod'] = $oPayName->get_app_display_name($item['paymethod']);
            }
        }
        //--end
        if($member_id){
             $row = $mem_adv->getList('log_id',array('member_id' => $member_id));
             $count = count($row);
        }
        $render = $app->render();
        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_advance';
        $this->controller->pagination($nPage,$count,$_GET);
        $render->pagedata['items_adv'] = $items_adv;
        $render->pagedata['member'] = $member->dump($member_id,'advance');
        return $render->fetch('admin/member/advance_list.html');
    }


    function detail_experience($member_id){
        $app = app::get('b2c');
        $member = $app->model('members');
        $aMem = $member->dump($member_id,'*',array('contact'=>array('*')));
        if($_POST){
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                $olddata = app::get('b2c')->model('members')->dump($member_id);
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
           if(!$member->change_exp($member_id,$_POST['experience'],$msg)){
                header('Content-Type:text/jcmd; charset=utf-8');
                echo '{error:"'.$msg.'",_:null}';
                exit;
           }
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                if(method_exists($obj_operatorlogs,'detail_experience_log')){
                    $newdata = app::get('b2c')->model('members')->dump($member_id);
                    $obj_operatorlogs->detail_experience_log($newdata,$olddata);
                }
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        }
        $aMem = $member->dump($member_id,'*',array('contact'=>array('*')));
        $render = $app->render();
        $render->pagedata['mem'] = $aMem;
        return $render->fetch('admin/member/experience.html');
    }


    function detail_point($member_id=null){
        if(!$member_id) return null;
        $nPage = $_GET['detail_point'] ? $_GET['detail_point'] : 1;
        $singlepage = $_GET['singlepage'] ? $_GET['singlepage']:false;
        $app = app::get('b2c');
        $member = $app->model('members');
        $mem_point = $app->model('member_point');
        $obj_user = kernel::single('desktop_user');

        if($_POST){
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                $olddata = app::get('b2c')->model('members')->dump($member_id);
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            $change_point = $_POST['modify_point'];
            $msg = $_POST['modify_remark'];
            if($mem_point->change_point($member_id,$change_point,$msg,'operator_adjust',3,0,$obj_user->user_id,'charge')){
                #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
                if($obj_operatorlogs = kernel::service('operatorlog.members')){
                    if(method_exists($obj_operatorlogs,'detail_point_log')){
                        $newdata = app::get('b2c')->model('members')->dump($member_id);
                        $obj_operatorlogs->detail_point_log($newdata,$olddata);
                    }
                }
                #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            }
            else{
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';
                    exit;
            }
        }
        if($member_id){
             $row = $mem_point->getList('id',array('member_id' => $member_id));
             $count = count($row);
        }
        $data = $member->dump($member_id,'*',array('score/event'=>array('*',null,array($this->pagelimit*($nPage-1),$this->pagelimit))));
        $accountObj = app::get('pam')->model('account');
        //获取日志操作管理员名称@lujy--start--
        foreach($data['score']['event'] as $key=>$val){
            $operatorInfo = $accountObj->getList('login_name',array('account_id' => $val['operator']));
            $data['score']['event'][$key]['operator_name'] = $operatorInfo['0']['login_name'];
        }
        //--end--
       //echo $nPage;
        $render = $app->render();
		$obj_extend_point = kernel::service('b2c.member_extend_point_info');
		if ($obj_extend_point)
		{
			// 当前会员拥有的积分
			$obj_extend_point->get_real_point($member_id, $data['score']['total']);
			// 当前会员实际可以使用的积分
			$obj_extend_point->get_usage_point($member_id, $data['score']['usage']);

            $render->pagedata['extends_html'] = $obj_extend_point->gen_extend_detail_point($member_id);
		}
		else
		{
			$data['score']['total'] = $mem_point->get_total_count($member_id);
			$data['score']['usage'] = $mem_point->get_total_count($member_id);
		}
        $render->pagedata['member'] = $data;
        $render->pagedata['event'] = $data['score']['event'];

        if($_GET['page']) unset($_GET['page']);
        $_GET['page'] = 'detail_point';
        $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/member/point_list.html');
    }


    function detail_order($member_id=null){
        if(!$member_id) return null;
        $nPage = $_GET['detail_order'] ? $_GET['detail_order'] : 1;
        $app = app::get('b2c');
        $member = $app->model('members');
         $orders = $member->getOrderByMemId($member_id,$this->pagelimit*($nPage-1),$this->pagelimit);
         $order =  $app->model('orders');
         if($member_id){
             $row = $order->getList('order_id',array('member_id' => $member_id));
             $count = count($row);
         }
         foreach($orders as $key=>$order1){
             $orders[$key]['status'] = $order->trasform_status('status',$orders[$key]['status']);
             $orders[$key]['pay_status'] = $order->trasform_status('pay_status',$orders[$key]['pay_status'] );
             $orders[$key]['ship_status'] = $order->trasform_status('ship_status', $orders[$key]['ship_status']);
         }

         $render = $app->render();
         $render->pagedata['orders'] = $orders;
         if($_GET['page']) unset($_GET['page']);
         $_GET['page'] = 'detail_order';
         $this->controller->pagination($nPage,$count,$_GET);
         return $render->fetch('admin/member/order.html');
    }


    function detail_msg($member_id){
        if(!$member_id) return null;
		$member_id = intval($member_id);
        $nPage = $_GET['detail_msg'] ? $_GET['detail_msg'] : 1;
        $app = app::get('b2c');
        $obj_msg = kernel::single('b2c_message_msg');
        $this->db = kernel::database();
        $_count_row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).')');
        $row = $this->db->select('select * from sdb_b2c_member_comments where has_sent="true" and object_type="msg" and (to_id ='.$this->db->quote($member_id).' or author_id='.$this->db->quote($member_id).') limit '.$this->pagelimit*($nPage-1).','.$this->pagelimit);
        $count = count($_count_row);
        $render = $app->render();
        $render->pagedata['msgs'] =  $row;
        if($_GET['page']) unset($_GET['page']);
         $_GET['page'] = 'detail_msg';
         $this->controller->pagination($nPage,$count,$_GET);
        return $render->fetch('admin/member/member_msg.html');
    }


    function detail_remark($member_id){
        $app = app::get('b2c');
        $member = $app->model('members');
        if($_POST){
            $sdf['remark'] = $_POST['remark'];
            $sdf['remark_type'] = $_POST['remark_type'];
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                $olddata = app::get('b2c')->model('members')->dump($member_id);
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            if(!$member->update($sdf,array('member_id' => $member_id))){
                    $msg = app::get('b2c')->_('保存失败!');
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{error:"'.$msg.'",_:null}';
                    exit;
            }
            #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
            if($obj_operatorlogs = kernel::service('operatorlog.members')){
                if(method_exists($obj_operatorlogs,'detail_remark_log')){
                    $newdata = app::get('b2c')->model('members')->dump($member_id);
                    $obj_operatorlogs->detail_remark_log($newdata,$olddata);
                }
            }
            #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
            if($_GET['singlepage']=='true'){
                 $msg = app::get('b2c')->_('保存成功!');
                    header('Content-Type:text/jcmd; charset=utf-8');
                    echo '{success:"'.$msg.'",_:null}';
                    exit;
            }
        }
        $remark = $member->getRemarkByMemId($member_id);
        $render = $app->render();
        $render->pagedata['remark_type'] = $remark['remark_type'];
        $render->pagedata['remark'] =  $remark['remark'];
        $render->pagedata['res_url'] = $app->res_url;
        return $render->fetch('admin/member/remark.html');
    }



    var $column_editbutton_order = 10;

    public function column_editbutton($row)
    {
        $render = $this->app->render();
        $arr = array(
            'app'=>$_GET['app'],
            'ctl'=>$_GET['ctl'],
            'act'=>$_GET['act'],
            'finder_id'=>$_GET['_finder']['finder_id'],
            'action'=>'detail',
            'finder_name'=>$_GET['_finder']['finder_id'],
        );

        $arr_link = array(
            'info'=>array(
                'detail_edit'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_edit&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('编辑会员信息'),
					'target'=>'tab',
                ),
            ),
            'finder'=>array(
                'detail_advance'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_advance&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('预存款'),
                    'target'=>'tab',
                ),
                'detail_experience'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_experience&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('经验值'),
                    'target'=>'tab',
                ),
                'detail_point'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_point&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('积分'),
                    'target'=>'tab',
                ),
                'detail_remark'=>array(
					'href'=>'javascript:void(0);',
                    'submit'=>'index.php?'.utils::http_build_query($arr).'&finderview=detail_remark&id='.$row['member_id'].'&_finder[finder_id]='.$_GET['_finder']['finder_id'],'label'=>app::get('b2c')->_('会员备注'),
                    'target'=>'tab',
                ),
            ),
        );

        //增加编辑菜单权限@lujy
        $permObj = kernel::single('desktop_controller');
        if(!$permObj->has_permission('editadvance')){
            unset($arr_link['finder']['detail_advance']);
        }
        if(!$permObj->has_permission('editexp')){
            unset($arr_link['finder']['detail_experience']);
        }
        if(!$permObj->has_permission('editadvance')){
            unset($arr_link['finder']['editscore']);
        }


        $site_get_policy_method = $this->app->getConf('site.get_policy.method');
        if ($site_get_policy_method == '1')
        {
            unset($arr_link['finder']['detail_point']);
        }

        $render->pagedata['arr_link'] = $arr_link;
        $render->pagedata['handle_title'] = app::get('b2c')->_('编辑');
        $render->pagedata['is_active'] = 'true';
        return $render->fetch('admin/actions.html');
    }

    var $column_uname_order = 11;
    public function column_uname($row){
        $pam_member_info = $this->userObject->get_members_data(array('account'=>'login_account'),$row['member_id']);
        $this->pam_member_info[$row['member_id']] = $pam_member_info;
        return $pam_member_info['account']['local'];
    }

    var $column_email_order = 12;
    public function column_email($row){
        if(!$this->pam_member_info[$row['member_id']]){
            $pam_member_info = $this->userObject->get_members_data(array('account'=>'login_account'),$row['member_id']);
        }else{
            $pam_member_info = $this->pam_member_info[$row['member_id']];
        }
        return $pam_member_info['account']['email'];
    }

    var $column_mobile_order = 13;
    public function column_mobile($row){
        if(!$this->pam_member_info[$row['member_id']]){
            $pam_member_info = $this->userObject->get_members_data(array('account'=>'login_account'),$row['member_id']);
        }else{
            $pam_member_info = $this->pam_member_info[$row['member_id']];
        }
        return $pam_member_info['account']['mobile'];
    }

    var $column_bind_tag_order = 14;
    var $column_bind_tag = '绑定账号平台';
    public function column_bind_tag($row){
        $data = app::get('pam')->model('bind_tag')->getList('tag_type',array('member_id'=>$row['member_id']));
        $schema = app::get('pam')->model('bind_tag')->schema;
        $tag_type = array();
        foreach($data as $row){
            if( empty($tag_type) || ($tag_type && !in_array($schema['columns']['tag_type']['type'][$row['tag_type']],$tag_type)) ){
                $tag_type[] = $schema['columns']['tag_type']['type'][$row['tag_type']];
            }
        }
        return implode('|',$tag_type);
    }

    var $column_weixin_nickname_order = 15;
    var $column_weixin_nickname = '微信昵称';
    public function column_weixin_nickname($row){
        $data = app::get('pam')->model('bind_tag')->getList('tag_type,tag_name',array('member_id'=>$row['member_id']));
        return $data[0]['tag_name'];
    }
}
