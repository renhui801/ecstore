<?php
/**
 * cps_ctl_site_welcome
 * 佣金报表控制器层类
 *
 * @uses cps_frontpage
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_site_welcome Jun 20, 2011  5:14:56 PM ever $
 */
class cps_ctl_site_welcome extends cps_frontpage {

    private $_ident_op = '#r-p';

    /**
     * 初始化构造
     * @access public
     * @param object $app
     * @version 1 Jun 23, 2011 创建
     */
    public function __construct(&$app) {
        parent::__construct($app);
        $this->_response->set_header('Cache-Control', 'no-store');
        $this->verifyUser();
    }

    /**
     * 快速获取代码
     */
    public function gencode() {
        $this->set_tmpl('cps_common');
        $this->page('site/gencode/index.html');
    }

    /**
     * 快速获取代码页
     * @access public
     */
    public function adlink_image() {
        //获取推广链接
        $arrAds = $this->app->model('adlink')->getAdLinkImageList();
        //设置页面显示数据
        $this->pagedata['data'] = $arrAds;

        $userObj = $this->app->model('users');
        $userInfo = $userObj->getUserById($this->app->cpsUserId,array('union_id'));

        $this->pagedata['union_str'] = $this->_ident_op.$userInfo['union_id'];

        //设置json数据
        $this->pagedata['jsdata'] = json_encode($arrAds);
        $this->set_tmpl('cps_common');
        $this->page('site/gencode/gencode.html');
    }

    /**
     * 自定义链接页
     * @access public
     */
    public function adlink_customize() {
        $userObj = $this->app->model('users');
        $userInfo = $userObj->getUserById($this->app->cpsUserId,array('union_id'));

        $this->pagedata['union_str'] = $this->_ident_op.$userInfo['union_id'];
        $this->set_tmpl('cps_common');
        $this->page('site/gencode/custom.html');
    }

    /**
     * 业绩首页
     * @access pubic
     * @version 1 Jul 8, 2011
     */
    public function profitIndex() {
        $this->set_tmpl('cps_common');
        $this->page('site/profit/index.html');
    }

    /**
     * 实时业绩查询与展示
     * @access public
     */
    public function profitSearch() {
        //获取时间段
        $start = strtotime($_GET['start']);
        $end = strtotime($_GET['end']);

        $tmp = (int)$this->_request->get_param(0);
        $start = $tmp ? $tmp : $start;
        $tmp = (int)$this->_request->get_param(1);
        $end = $tmp ? $tmp : $end;

        //联盟商订单佣金模型
        $mdlUop = $this->app->model('userorderprofit');

        //每页条数
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 10);

        //页码
        $page = (int)$this->_request->get_param(2);
        $page || $page=1;

        //订单佣金状态
        $uopStates = $mdlUop->schema['columns']['state']['type'];

        if ($start && $end) {
            //所有有效订单数据
            $strSql = 'SELECT *
            		FROM sdb_cps_userorderprofit
            		WHERE u_id = ' . $this->app->cpsUserId . ' AND addtime > ' . $start . '
            		AND addtime < ' . ($end + 86400);
            $all = $mdlUop->db->select($strSql);

            //显示数据
            $list = array();
            //统计数据
            $total = array('num' => 0, 'totalCost' => 0, 'totalMoney' => 0);
            //起始索引
            $from = $pageLimit*($page - 1);
            //末索引+1
            $to = $from + $pageLimit;

            $i = 0;
            foreach ($all as $k => $v) {
                if ($total['num'] >= $from && $total['num'] < $to) {
                    $list[$i] = $v;
                    //格式化数据
                    $list[$i]['addtime'] = date('Y-m-d H:i', $v['addtime']);
                    $list[$i]['order_cost'] = sprintf('%0.2f', $v['order_cost']);
                    $list[$i]['money'] = sprintf('%0.2f', $v['money']);
                    $list[$i]['state'] = $uopStates[$v['state']];
                    $list[$i]['flag'] = $v['state']; //高亮显示标识
                    $i++;
                }

                //统计数据
                $total['num']++;
                if ($v['state'] == '2') {
                    $total['totalCost'] += $v['order_cost'];
                    $total['totalMoney'] += $v['money'];
                }
            }

            //格式化数字
            $total['totalCost'] = sprintf('%0.2f', $total['totalCost']);
            $total['totalMoney'] = sprintf('%0.2f', $total['totalMoney']);

            $this->pagedata['start'] = date('Y-n-j', $start);
            $this->pagedata['end'] = date('Y-n-j', $end);

            $this->pagedata['isSearch'] = true;
        }

        $token = md5('page' . $page);
        //分页条
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>ceil($total['num']/$pageLimit),
                'link'=>$this->gen_url(array('app'=>'cps', 'ctl'=>'site_welcome', 'act'=>'profitSearch', 'arg0' => $start, 'arg1' => $end, 'arg3'=>$token)),
                'token'=>$token
        );
        $this->pagedata['total'] = $total;
        $this->pagedata['list'] = $list;
        $this->set_tmpl('cps_common');
        $this->page('site/profit/detail.html');
    }

    /**
     * 实时业绩查询结果导出
     * @access public
     */
    public function profitExport() {
        //获取时间段
        $start = strtotime($_GET['start']);
        $end = strtotime($_GET['end']);

        //csv工具实例
        $csv = kernel::single('desktop_io_type_csv');
        //联盟商订单佣金模型
        $mdlUop = $this->app->model('userorderprofit');

        $data = array('name'=> 'userorderprofit');

        $data['title'] = '订单号,下单时间,来源,订单金额,佣金';

        if ($start && $end) {
            //所有有效订单数据
            $strSql = 'SELECT order_id,addtime,refer_url,order_cost,money
            		FROM sdb_cps_userorderprofit
            		WHERE u_id = ' . $this->app->cpsUserId . ' AND state = \'2\' AND addtime > ' . $start . '
            		AND addtime < ' . ($end + 86400);
            $rows = $mdlUop->db->select($strSql);
        }

        foreach( $rows as $line => $row ){
            $rowVal = array();

            $row['addtime'] = date('Y-m-d H:i', $row['addtime']);
            $row['order_cost'] = sprintf('%0.2f', $row['order_cost']);
            $row['money'] = sprintf('%0.2f', $row['money']);

            $rowVal = array($row['order_id'],$row['addtime'],$row['refer_url'],$row['order_cost'],$row['money']);

            $data['contents'][] = '"'.implode('","',$rowVal).'"';
        }
        //导出
        $csv->export($data, $mdlUop);
        $csv->export_header($data, $mdlUop);
    }

    /**
     * 实时业绩查询订单详情
     * @access public
     * @by zhaojingna
     */
     public function orderDetail(){
     	$o = app::get('b2c')->model('order_objects');
        $sql = "SELECT b.goods_id,a.name,a.quantity as nums
            FROM sdb_b2c_order_objects AS a LEFT JOIN sdb_b2c_goods AS b ON a.goods_id = b.goods_id
            WHERE a.order_id = {$_POST['order_id']} AND obj_type = 'goods'";
        $tmp = $o->db->select($sql);
     	echo json_encode($tmp);
     }

    /**
     * 订单详情查询与展示
     * @access public
     */
    public function orderSearch() {
        $orderId = $_GET['orderId'];
        if ($orderId) {
            //联盟商订单佣金模型
            $mdlUop = $this->app->model('userorderprofit');
            $strSql = 'SELECT * FROM sdb_cps_userorderprofit
            		WHERE order_id = ' . $orderId . ' AND u_id = ' . $this->app->cpsUserId;
            $data = $mdlUop->db->selectrow($strSql);

            //获取订单商品
            $o = app::get('b2c')->model('order_objects');
            $sql = "SELECT b.goods_id,a.name,a.quantity as nums
                FROM sdb_b2c_order_objects AS a LEFT JOIN sdb_b2c_goods AS b ON a.goods_id = b.goods_id
                WHERE a.order_id = {$orderId} AND obj_type = 'goods'";
            $tmp = $o->db->select($sql);

            if ($data) {
                //订单佣金状态
                $uopStates = $mdlUop->schema['columns']['state']['type'];

                //格式化数据
                $data['order_cost'] = sprintf('%0.2f', $data['order_cost']);
                $data['money'] = sprintf('%0.2f', $data['money']);
                $data['addtime'] = date('Y-m-d H:i', $data['addtime']);
                $data['state'] = $uopStates[$data['state']]; //状态显示
            }

            $this->pagedata['isSearch'] = true;
        }
        //获取订单数据
        $this->pagedata['data'] = $data;
        $this->pagedata['goods'] = $tmp;
        $this->set_tmpl('cps_common');
        $this->page('site/profit/order.html');
    }

    /**
     * 收益月报表查询与展示
     * @access public
     */
    public function incomeSearch() {
        //获取时间段
        $year = $_GET['year'];
        $month = $_GET['month'];

        $tmp = (int)$this->_request->get_param(0);
        $year = $tmp ? $tmp : $year;
        $tmp = (int)$this->_request->get_param(1);
        $month = $tmp ? $tmp : $month;

        //年下拉菜单
        $y = date('Y', time());
        $ys = '';
        for ($k = 2011; $k <= $y; $k++) {
            $s = '';
            if ($k == $year) {
                $s = 'selected="selected"';
            }
            $ys .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
        }
        $this->pagedata['years'] = $ys;

        //月下拉菜单
        $ms = '';
        for ($k = 1; $k < 13; $k++) {
            $s = '';
            if ($k == $month) {
                $s = 'selected="selected"';
            }
            $ms .= '<option value="' . $k . '" ' . $s . '>' . $k . '</option>';
        }
        $this->pagedata['months'] = $ms;

        //联盟商订单佣金模型
        $mdlUop = $this->app->model('userorderprofit');
        //联盟商月度佣金模型
        $mdlUmp = $this->app->model('usermonthprofit');

        //每页条数
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 10);

        //页码
        $page = (int)$this->_request->get_param(2);
        $page || $page=1;

        if ($year && $month) {
            //月度佣金数据
            $strSql = 'SELECT * FROM sdb_cps_usermonthprofit WHERE u_id = ' . $this->app->cpsUserId . ' AND year=' . $year . ' AND month=' . $month;
            $total = $mdlUmp->db->selectrow($strSql);

            //格式化数据
            $total['cost_sum'] = sprintf('%0.2f', $total['cost_sum']);
            $total['money_sum'] = sprintf('%0.2f', $total['money_sum']);

            //格式化年月
            $yam = $year . sprintf('%02d', $month);
            //所有有效订单数据
            $strSql = 'SELECT *
            		FROM sdb_cps_userorderprofit
            		WHERE u_id = ' . $this->app->cpsUserId . ' AND state = \'2\' AND yam = ' . $yam;
            $all = $mdlUop->db->select($strSql);
            //显示数据
            $list = array();
            //起始索引
            $from = $pageLimit*($page - 1);
            //末索引+1
            $to = $from + $pageLimit;

            $i = 0;
            $j = 0;
            foreach ($all as $k => $v) {
                if ($j >= $from && $j < $to) {
                    $list[$i] = $v;
                    //格式化数据
                    $list[$i]['addtime'] = date('Y-m-d H:i', $v['addtime']);
                    $list[$i]['order_cost'] = sprintf('%0.2f', $v['order_cost']);
                    $list[$i]['money'] = sprintf('%0.2f', $v['money']);
                    $i++;
                }
                $j++;
            }

            $this->pagedata['isSearch'] = true;
        }

        $token = md5('page' . $page);
        //分页条
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>ceil($total['order_sum']/$pageLimit),
                'link'=>$this->gen_url(array('app'=>'cps', 'ctl'=>'site_welcome', 'act'=>'incomeSearch', 'arg0' => $year, 'arg1' => $month, 'arg3'=>$token)),
                'token'=>$token
        );
        $this->pagedata['total'] = $total;
        $this->pagedata['list'] = $list;
        $this->set_tmpl('cps_common');
        $this->page('site/profit/month.html');
    }

    /**
     * 联盟商账户信息首页
     * @access public
     * @version 3 Jul 15, 2011
     */
    public function showUser() {
        //联盟商月度佣金模型
        $mdlUmp = $this->app->model('usermonthprofit');
        //信息模型
        $mdlInfo = $this->app->model('info');
        //用户模型
        $mdlUser = $this->app->model('users');

        $tm = time() - 2592000;
        $year = date('Y', $tm);
        $month = date('m', $tm);

        //上个月已发放佣金
        $rsGrant = $mdlUmp->dump(array('u_id' => $this->app->cpsUserId, 'year' => $year, 'month' => $month, 'state' => '2'), 'money_sum');
        //所有未发放佣金
        $user = $mdlUser->dump($this->app->cpsUserId);

        //公告信息
        $arrNotice = $mdlInfo->getList('title, info_id', array('i_type' => '1', 'ifpub' => 'true', 'pubtime|lthan' => time()), 0, 5, 'pubtime DESC');
        //常见问题
        $arrFaq = $mdlInfo->getList('title, info_id', array('i_type' => '2'), 0, 5);

        $this->pagedata['grant'] = sprintf('%0.2f', $rsGrant['money_sum']);
        $this->pagedata['ungrant'] = sprintf('%0.2f', $user['profit']);
        $this->pagedata['notices'] = $arrNotice;
        $this->pagedata['faqs'] = $arrFaq;
        $this->set_tmpl('cps_common');
        $this->page('site/user/index.html');
    }

    /**
     * 联盟商账户信息
     * @access public
     * @version 1 Jul 12, 2011
     */
    public function showUserInfo() {
        $this->set_tmpl('cps_common');
        $this->page('site/user/user.html');
    }

    /**
     * 修改网站信息
     * @access public
     * @version 1 Jul 7, 2011
     */
    public function user_webinfo_edit() {
        //网站模型
        $mdlWeb = $this->app->model('userweb');

        //修改网站信息
        if ($_POST) {
            //接收页面数据
            $web = $_POST['web'];
            //网站与联盟商关联
            $web['u_id'] = $this->app->cpsUserId;

            //修改网站结果处理
            if ($mdlWeb->save($web)) {
                $this->splash('success', $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'user_webinfo_edit'), '修改成功'));
            } else {
                $this->splash('failed', $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'user_webinfo_edit'), '修改失败'));
            }
        } else {//显示页面
            $webTypes = $mdlWeb->getWebType();
            $web = $mdlWeb->getUserWebById($this->app->cpsUserId);
            $this->pagedata['webTypes'] = $webTypes;
            $this->pagedata['web'] = $web;
            $this->set_tmpl('cps_common');
            $this->page('site/user/user_web.html');
        }
    }

    /**
     * 修改联系方式
     * @access public
     * @version 1 Jul 7, 2011
     */
    public function user_contactinfo_edit() {
        //联盟商模型
        $mdlUser = $this->app->model('users');

        //修改账户
        if ($_POST) {
            //接收页面数据
            $user = $_POST['user'];
            //联系方式与联盟商关联
            $user['u_id'] = $this->app->cpsUserId;

            //修改联系方式结果处理
            if ($mdlUser->save($user)) {
                $this->splash('success', $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'user_contactinfo_edit'), '修改成功'));
            } else {
                $this->splash('failed', $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'user_contactinfo_edit'), '修改失败'));
            }
        } else { //显示页面
            $user = $mdlUser->getUserById($this->app->cpsUserId);
            //联系方式信息
            $this->pagedata['user'] = $user;
            $this->set_tmpl('cps_common');
            $this->page('site/user/user_contact.html');
        }
    }

    /**
     * 修改收款账户
     * @access public
     * @version 1 Jul 7, 2011
     */
    public function user_pay_account_edit() {
        //账户模型
        $mdlAcc = $this->app->model('userpayaccount');

        //修改账户
        if ($_POST) {
            //接收页面数据
            $acc = $_POST['acc'];
            //账户与联盟商关联
            $acc['u_id'] = $this->app->cpsUserId;

            //修改账户结果处理
            if ($mdlAcc->save($acc)) {
                $this->splash('success', $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'user_pay_account_edit'), '修改成功'));
            } else {
                $this->splash('failed', $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'user_pay_account_edit'), '修改失败'));
            }
        } else {
            //联盟商模型
            $mdlUser = $this->app->model('users');
            //银行模型
            $mdlBank = $this->app->model('bank');
            //获取账户
            $acc = $mdlAcc->getUserPayAccountById($this->app->cpsUserId);
            //获取当前用户类型
            $user = $mdlUser->getUserById($this->app->cpsUserId, array('u_type'));
            //获取开户银行
            $banks = $mdlBank->getBankList(array('is_use' => 'true', 'disabled' => 'false'));
            //获取所有用户类型
            $userTypes = $mdlUser->getUserTypes();
            $this->pagedata['acc'] = $acc;
            $this->pagedata['banks'] = $banks;
            $this->pagedata['userType'] = $userTypes[$user['u_type']];
            $this->pagedata['userTypeId'] = $user['u_type'];
            $this->set_tmpl('cps_common');
            $this->page('site/user/user_account.html');
        }
    }


    /**
     * 修改密码
     * @access public
     */
    public function user_password_edit() {
        if ($_POST) {
            $mdlUser = $this->app->model('users');
            $url = $this->gen_url(array('app' => 'cps', 'ctl' => 'site_welcome', 'act' => 'user_password_edit'));
            //修正密码判断走model定义的通用方法
            if (!$mdlUser->validate_password($_POST, $msg)) {
                $this->splash('failed', $url, $msg);
            }
            $mdlAcc = app::get('pam')->model('account');
            $rows = $mdlAcc->getList('account_id', array('account_id'=>$this->app->cpsUserId, 'login_password'=>pam_encrypt::get_encrypted_password($_POST['old_passwd'],pam_account::get_account_type($this->app->app_id))));
            if ($rows[0]) {
                if ($mdlAcc->update(array('login_password' => pam_encrypt::get_encrypted_password($_POST['password'], pam_account::get_account_type($this->app->app_id))), array('account_id' => $this->app->cpsUserId))) {
                    $this->splash('success', $url, '修改成功');
                } else {
                    $this->splash('failed', $url, '修改失败');
                }
            } else {
                $this->splash('failed', $url, '旧密码输入不正确');
            }
        } else {
            $this->set_tmpl('cps_common');
            $this->page('site/user/user_password.html');
        }
    }
}
