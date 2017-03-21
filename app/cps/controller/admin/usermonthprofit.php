<?php
/**
 * cps_ctl_admin_usermonthprofit
 * 后台佣金报表控制层类
 *
 * @uses desktop_controller
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_admin_usermonthprofit Jun 20, 2011  3:44:44 PM ever $
 */
class cps_ctl_admin_usermonthprofit extends desktop_controller {

    public $workground = 'cps_center';

    /**
     * 初始化构造方法
     * @param object $app
     * @access public
     * @version 1 Jun 23, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 佣金报表与未发放佣金展示页
     * @access public
     * @param $state 发放状态
     * @version 1 Jun 23, 2011 创建
     */
    public function index($state = null) {
        //按钮栏按钮
        $actions = null;
        //标题
        $ttl = '月度佣金报表';
        //未发放佣金显示批量发放佣金按钮
        if ($state == '1') {
            $actions = array(
	            array(
	               	'label'=>$this->app->_('确认发放'),
	                'confirm' => $this->app->_('确定发放佣金？'),
	                'submit'=>'index.php?app=cps&ctl=admin_usermonthprofit&act=grant',
	            ),
            );
            //未发放佣金标题
            $ttl = '未发放佣金';
        }

        //列表页面参数
        $params = array(
            'title'=>$this->app->_($ttl),
            'actions'=>$actions,
            'use_buildin_new_dialog' => false,
            'use_buildin_set_tag' => false,
            'use_buildin_recycle' => false,
            'use_buildin_export' => true,
            'use_buildin_import' => false,
            'use_buildin_filter' => true,
            'use_buildin_setcol' => true,
            'use_buildin_refresh' => true,
            'use_buildin_selectrow' => true,
            'use_buildin_tagedit' => false,
            'use_view_tab' => true,
            'allow_detail_popup' => false,
        );

        if ($state) {
            $params['base_filter'] = array('state' => $state);
        }
        $this->finder('cps_mdl_usermonthprofit', $params);
    }

    /**
     * 发放佣金
     * @param int $umpId 月记录id
     * @version 2 Jul 15, 2011
     */
    public function grant($umpId) {
        //接收批量提交数据
        $umpIds = $_POST['ump_id'];
        //接收单个数据
        $umpIds[] = $umpId;

        //联盟商月度佣金模型
        $mdlUmp = $this->app->model('usermonthprofit');
        //联盟商模型
        $mdlUser = $this->app->model('users');
        //联盟商月度佣金
        $umps = $mdlUmp->getList('ump_id, state, u_id, money_sum', array('ump_id' => $umpIds));
        //开启事务
        $this->begin();
        
        //未发放月度佣金记录
        $gUmps = array();
        foreach ($umps as $row) {
            //判断月度佣金记录未发放
            if ($row['state'] == '1') {
                $gUmps[] = $row;
            }
        }

        //未发放月度佣金进行发放
        if ($gUmps) {
            foreach ($gUmps as $ump) {
                //联盟商信息
                $user = $mdlUser->getUserById($ump['u_id']);
                //添加历史佣金金额
                $user['history_profit'] += $ump['money_sum'];
                //修改未发放佣金金额
                $user['profit'] -= $ump['money_sum'];
                //更新佣金记录
                $rsUmp = $mdlUmp->update(array('state' => '2'), array('ump_id' => $ump['ump_id']));
                //更新联盟商记录
                $rsUser = $mdlUser->update(array('history_profit' => $user['history_profit'], 'profit' => $user['profit']), array('u_id' => $ump['u_id']));
            }
            //事务结束
            $this->end(true, '发放成功', 'index.php?app=cps&ctl=admin_usermonthprofit&act=index&p[0]=1');
        } else {
            $this->end(false, '月度佣金已发放', 'index.php?app=cps&ctl=admin_usermonthprofit&act=index&p[0]=1');
        }
    }
}