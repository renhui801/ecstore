<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 * @author chenping
 * @version 2011-8-8 17:44
 */

class emailsubs_ctl_admin_emailcont extends desktop_controller{

    var $workground = 'emailsubs_ctl_admin_emailcont';

    function __construct(&$app) {
        parent::__construct($app);
        $this->_request = kernel::single('base_component_request');
    }

    /**
     * @access public
     * @description 订阅邮件列表
     * @param void
     * @return void
     */
    public function index() {
        $this->finder('emailsubs_mdl_emailcont',array(
            'title'=>$this->app->_('订阅邮件模板列表'),
            'actions'=>array(
                        array('label'=>$this->app->_('添加邮件模板'),
                        'icon'=>'add.gif',
                        'href'=>'index.php?app=emailsubs&ctl=admin_emailcont&act=addNew'),
                        ),
                        'use_buildin_filter'=>true,
            ));
    }

    /**
     * @access public
     * @description 新增订阅邮件
     * @param void
     * @return void
     */
    public function addNew() {
        $this->path[] = array('text'=>$this->app->_('新增订阅邮件'));
        $this->page('admin/emailcont/addNew.html');
    }

    /**
     * @access public
     * @description 保存订阅邮件内容
     * @param void
     * @return void
     */
    public function save() {
        $params = $this->_request->get_post();
        $url = $params['ec_id'] ? 'index.php?app=emailsubs&ctl=admin_emailcont&act=showEdit&p[0]='.$params['ec_id'] : 'index.php?app=emailsubs&ctl=admin_emailcont&act=addNew';
        $this->begin($url);
        $params['ec_content'] = htmlspecialchars_decode($params['ec_content']);
        if(!$params['ec_id']){
            $params['ec_addtime'] = time();
        }
        $emailcontModel = $this->app->model('emailcont');
        $result = $emailcontModel->save($params);
        $this->end(true,$this->app->_('操作成功'),'index.php?app=emailsubs&ctl=admin_emailcont&act=index');
    }

    /**
     * @access public
     * @description 编辑订阅邮件
     * @param void
     * @return void
     */
    public function showEdit($ec_id) {
        $this->path[] = array('text'=>$this->app->_('编辑订阅邮件'));
        $emailcontModel = $this->app->model('emailcont');
        $this->pagedata['emailcont'] = $emailcontModel->dump($ec_id);
        $this->page('admin/emailcont/showEdit.html');
    }

    /**
     * @access public
     * @description 订阅邮件预览
     * @param void
     * @return void
     */
    public function preview($ec_id) {
        $this->path[] = array('text'=>$this->app->_('订阅邮件预览'));
        $params = $this->_request->get_post();
        if($ec_id) {
            $emailcontModel = $this->app->model('emailcont');
            $emailcont = $emailcontModel->dump($ec_id);
        }else{
            $emailcont = $params;
        }
        $emailtmplModel = $this->app->model('emailtmpl');
        $emailtmpl = $emailtmplModel->dump('unmem');

        $pattern = array('{emailsubs_content}','{shopname}');
        $subject = array($emailcont['ec_content'], app::get('site')->getConf('site.name'));
        $preview_content = str_replace($pattern,$subject,$emailtmpl['et_content']);
        $this->pagedata['preview_content'] = $preview_content;

        $this->display('admin/emailcont/preview.html');
    }

    /**
     * @access public
     * @description 获取更多邮件模板
     * @param void
     * @return void
     */
    public function getMoreCont($page=1) {
        $this->path[] = array('text'=>$this->app->_('更多邮件模板'));
        $pageLimit = 10;
        $filter = array();

        //过滤条件
        $params = $this->_request->get_post();
        if(isset($params['ec_title'])) {
            $filter['ec_title'] = $params['ec_title'];
        }

        $emailcontModel = $this->app->model('emailcont');
        $emailcontList = $emailcontModel->getList('ec_id,ec_title',$filter,($page-1)*$pageLimit,$pageLimit,'ec_addtime DESC');
        $this->pagedata['emailcont'] = $emailcontList;

        $count = $emailcontModel->count($filter);
        $this->_pager($page,ceil($count/$pageLimit));
        $this->display('admin/emailcont/morecont.html');
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
            'link'=>"index.php?app=emailsubs&ctl=admin_emailcont&act=getMoreCont&p[0]=%d"
            ));
        $this->pagedata['pager'] = $pager;
    }
}
