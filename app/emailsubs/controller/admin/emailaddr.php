<?php
/**
 * ShopEx网上商店 邮件订阅邮箱地址类
 *
 *
 * @package admin
 * @version $Id 2011-8-11 15:26$
 * @author <chenping@shopex.cn>
 * @copyright 2003-2008 Shanghai ShopEx Network Tech. Co., Ltd.
 * @license Commercial
 * =================================================================
 * 版权所有 (C) 2003-2009 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址:http://www.shopex.cn/
 * -----------------------------------------------------------------
 * 您只能在不用于商业目的的前提下对程序代码进行修改和使用；
 * 不允许对程序代码以任何形式任何目的的再发布。
 * =================================================================
 */

class emailsubs_ctl_admin_emailaddr extends desktop_controller{

    var $workground = 'emailsubs_ctl_admin_emailcont';

     function __construct(&$app){
        parent::__construct($app);
        $this->_request = kernel::single('base_component_request');
     }

     /**
      * @description 得到EMAIL
      * @param void
      * @return void
      */
     public function getMoreEmail($page=1) {
        $this->path[] = array('text'=>$this->app->_('订阅邮件地址'));
        $pageLimit = 10;
        $filter = array();

        //过滤条件
        $params = $this->_request->get_post();
        if(isset($params['ea_email'])) {
            $filter['ea_email'] = $params['ea_email'];
            $filter['_ea_email_search'] = 'has';
        }

        $emailaddrModel = $this->app->model('emailaddr');
        $emailaddrList = $emailaddrModel->getList('ea_id,ea_email',$filter,($page-1)*$pageLimit,$pageLimit);
        $this->pagedata['emailaddr'] = $emailaddrList;

        $count = $emailaddrModel->count($filter);
        $this->_pager($page,ceil($count/$pageLimit));

        $this->display('admin/emailaddr/moremail.html');
     }

    /**
     * @access public
     * @description 分页处理
     * @param void
     * @return void
     */
    private function _pager($current,$total) {
        $ui = kernel::single('base_component_ui');
        $pager = $ui->pager(array(
            'current'=>$current,
            'total'=>$total,
            'link'=>"index.php?app=emailsubs&ctl=admin_emailaddr&act=getMoreEmail&p[0]=%d"
            ));
        $this->pagedata['pager'] = $pager;
    }
 }