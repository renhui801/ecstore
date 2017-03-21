<?php
/**
 * cps_ctl_site_info
 * 前台文章相关展示的控制层类
 *
 * @uses cps_frontpage
 * @package CPS
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_ctl_site_info Jun 20, 2011  5:09:55 PM ever $
 */
class cps_ctl_site_info extends cps_frontpage {

    /**
     * 初始化构造方法
     * @access public
     * @param object $app
     * @version Jun 22, 2011 创建
     */
    public function __construct($app) {
        parent::__construct($app);
    }

    /**
     * 展示特定类型的文章列表
     * @access public
     */
    public function lists() {
        $this->begin($this->gen_url(array('app'=>'welcome', 'ctl'=>'index')));
        $info_list_id = $this->_request->get_param(0);
        if(empty($info_list_id)){
            $this->end(false, app::get('cps')->_('访问出错'));
        }

        $filter = array('i_type'=>$info_list_id);

        //每页条数
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 10);

        //当前页
        $page = (int)$this->_request->get_param(1);
        $page or $page=1;
        $filter['ifpub'] = 'true';
        $filter['pubtime|sthan'] = time();

        $infoObj = $this->app->model('info');

        //总数
        $count = $infoObj->count($filter);
        $arr_infos = $infoObj->getInfoList('*', $filter, $pageLimit*($page-1),$pageLimit, 'pubtime DESC');

        //标识用于生成url
        $token = md5("page{$page}");
        $this->pagedata['pager'] = array(
                'current'=>$page,
                'total'=>ceil($count/$pageLimit),
                'link'=>$this->gen_url(array('app'=>'cps', 'ctl'=>'site_info', 'act'=>'lists', 'arg0'=>$info_list_id, 'arg2'=>$token)),
                'token'=>$token
        );

        $filter = array();
        $filter['ifpub'] = 'true';
        $filter['pubtime|than'] = time();
        $arr = $infoObj->getInfoList( 'pubtime',$filter,0,1,' pubtime ASC' );
        if( $arr ) { //设置缓存过期时间
            reset( $arr );
            $arr = current($arr);
            cachemgr::set_expiration($arr['pubtime']);
        }

        $this->pagedata['info_type'] = $info_list_id;
        $this->pagedata['infos'] = $arr_infos;

        $this->set_tmpl('cps_common');
        $this->page('site/notice/list.html');
    }

    /**
     * 单个文章展示详细页
     * @access public
     * @param int $index 文章id
     * @version Jun 22, 2011 创建
     */
    public function index($infoId) {
        $this->begin($this->gen_url(array('app'=>'welcome', 'ctl'=>'index')));
        $article_id = $this->_request->get_param(0);

        //文章模型
        $mdlInfo = $this->app->model('info');
        if($article_id > 0){

            //文章详细信息
            $detail = kernel::single('cps_info_detail')->get_detail($article_id, true);

            if($detail['ifpub']=='true' && $detail['pubtime'] <= time()){

                $this->pagedata['info'] = $detail;
                $this->set_tmpl('cps_notice');
                $this->page('site/notice/detail.html');

            }else{
                $this->end(false, app::get('cps')->_('文章未发布！'));
            }
        }else{
            $this->end(false, app::get('cps')->_('访问出错！'));
        }
    }

    /**
     * 帮助中心展示页
     * @access public
     * @version Jun 22, 2011 创建
     */
    public function showHelp() {
        $filter['i_type'] = '2';
        $filter['ifpub'] = 'true';
        //$filter['disabled'] = 'false';
        $filter['pubtime|lthan'] = time();

        $infoObj = $this->app->model('info');
        $arr_faqs = $infoObj->getInfoList('*', $filter);

        $this->pagedata['faqs'] = $arr_faqs;
        $this->set_tmpl('cps_common');
        $this->page('site/help/index.html');
    }

}