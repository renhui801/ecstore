<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class content_ctl_wap_article extends wap_frontpage
{
    public function index()
    {
        $nav_url = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:$this->gen_url(array('app'=>'wap', 'ctl'=>'default','act'=>'index'));
        $this->begin($nav_url);
        $article_id = $this->_request->get_param(0);
        if($article_id > 0){
            $detail = kernel::single('content_article_detail')->get_detail($article_id, true);
            if($detail['indexs']['ifpub']=='true' && $detail['indexs']['pubtime'] <= time()){
                $oCAN = kernel::single('content_article_node');
                $node_info = $oCAN->get_node($detail['indexs']['node_id'], true);
                if($node_info['ifpub'] == 'true'){
                    $aPath = $oCAN->get_node_path($detail['indexs']['node_id'], true);
                    $aPath[] = array(
                        'link'  => $this->app->router()->gen_url(array('app'=>'content', 'ctl'=>'wap_article', 'act'=>'index', 'arg0'=>$article_id)),
                        'title' => $detail['indexs']['title'],
                    );
                    $GLOBALS['runtime']['path'] = $aPath;
                    //title keywords description
                    $this->get_seo_info($detail['bodys'], $aPath);
                    unset($aPath);
                    if($this->weixin_share_page == null)
                        $this->weixin_share_page = array();
                    if(in_array('article-index', $this->weixin_share_page)){
                        $this->pagedata['from_weixin'] = $this->from_weixin;
                        $this->pagedata['weixin']['appid'] = $this->weixin_a_appid;
                        $this->pagedata['weixin']['imgUrl'] = base_storager::image_path(app::get('weixin')->getConf('weixin_basic_setting.weixin_logo'));
                        $this->pagedata['weixin']['linelink'] = app::get('wap')->router()->gen_url(array('app'=>'content','ctl'=>'wap_article','act'=>'index','arg0'=>$article_id, 'full'=>1));
                        $this->pagedata['weixin']['shareTitle'] = $this->pagedata['title'];
                        $this->pagedata['weixin']['descContent'] = $this->pagedata['description'];
                    }
                    switch($detail['indexs']['type'])
                    {
                        case 1:
                            $this->_index1($detail);
                        break;
                        case 2:
                            $this->_index2($detail);
                        break;
                        case 3:
                            $this->_index3($detail);
                        break;
                        default:
                    }//End Switch
                } else {
                    $this->end(false, app::get('content')->_('文章节点未发布！'));
                }
            } else {
                $this->end(false, app::get('content')->_('文章未发布！'));
            }
        } else {
            $this->end(false, app::get('content')->_('访问出错！'));
        }
    }//End Function

    private function _index1($detail)
    {
        $this->pagedata['detail'] = $detail;
        $this->pagedata['content'] = kernel::single('content_article_detail')->parse_hot_link($detail['bodys']);
        $this->set_tmpl('article');
        $this->set_tmpl_file($detail['bodys']['tmpl_path']);
        $this->page('wap/article/index.html');
    }//End Function

    private function _index2($detail)
    {
        $this->set_tmpl_file($detail['bodys']['tmpl_path']);
        $this->page('content:' . $detail['indexs']['article_id']);
    }//End Function

    private function _index3($detail)
    {
        if(preg_match('/^\[header\]/', trim($detail['bodys']['content']))){
            $this->set_tmpl_file('block/header.html');
            $this->page('wap/article/empty.html');
        }//头
        $this->page('content:' . $detail['indexs']['article_id'], true);
        if(preg_match('/\[footer\]$/', trim($detail['bodys']['content']))){
            $this->set_tmpl_file('block/footer.html');
            $this->page('wap/article/empty.html');
        }//尾
    }//End Function

    public function l/*lists*/() {
        $nav_url = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:$this->gen_url(array('app'=>'wap', 'ctl'=>'default','act'=>'index'));
        $this->begin($nav_url);
        $art_list_id = $this->_request->get_param(0);
        if(empty($art_list_id)){
            $this->end(false, app::get('content')->_('访问出错'));
        }
        $oCAN = kernel::single('content_article_node');

        $aPath = $oCAN->get_node_path($art_list_id, true);
        $info = $oCAN->get_node($art_list_id, true);
        if($info['ifpub']!='true') {
            $this->end(false, app::get('content')->_('未发布！错误访问!'));
        }
        $GLOBALS['runtime']['path'] = $aPath;

        //title keywords description
        $this->get_seo_info($info, $aPath);

        $filter = array('node_id'=>$art_list_id);

        //每页条数
        $pageLimit = $this->app->getConf('gallery.display.listnum');
        $pageLimit = ($pageLimit ? $pageLimit : 10);

        //当前页
        $page = (int)$this->_request->get_param(1);
        $page or $page=1;
        $filter['ifpub'] = 'true';
        $filter['platform'] = 'wap';
        $filter['pubtime|sthan'] = time();

        $indexsObj = $this->app->model('article_indexs');
        $bodysObj = $this->app->model('article_bodys');

        //总数
        $count = $indexsObj->count($filter);
        $arr_articles = $indexsObj->getList('*', $filter, $pageLimit*($page-1),$pageLimit, 'pubtime DESC');
        $article_ids = array();
        foreach((array)$arr_articles as $key=>$art){
            $article_ids[] = $art['article_id'];
            $tmp_arr_articles[$art['article_id']] = $art;
        }

        if($article_ids)
        {
            $sql = "SELECT a.* , b.storage, b.s_url FROM `sdb_content_article_bodys` a LEFT JOIN `sdb_image_image` b ON a.image_id = b.image_id WHERE a.article_id IN (" .join(',' , $article_ids).") order by a.article_id desc";
            $arr_article_bodys = kernel::database()->select($sql);
        }

        //print_r($arr_article_bodys);
        $arr_articles = $arr_image_ids = array();
        foreach((array)$arr_article_bodys as $key=>$art){
            $art['s_url'] = '/'.$art['s_url'];
            $arr_articles[] = array_merge($tmp_arr_articles[$art['article_id']],$art);
            $arr_image_ids[] = $art['image_id'];
        }

        //标识用于生成url
        $token = md5("page{$page}");
        $this->pagedata['pager'] = array(
            'current'=>$page,
            'total'=>ceil($count/$pageLimit),
            'link'=>$this->gen_url(array('app'=>'content', 'ctl'=>'wap_article', 'act'=>'l', 'arg0'=>$art_list_id, 'arg2'=>$token)),
            'token'=>$token
        );

        $filter = array();
        $filter['ifpub'] = 'true';
        $filter['pubtime|than'] = time();
        $arr = $indexsObj->getList( 'pubtime',$filter,0,1,' pubtime ASC' );
        if( $arr ) { //设置缓存过期时间
            reset( $arr );
            $arr = current($arr);
            cachemgr::set_expiration($arr['pubtime']);
        }

        $this->pagedata['cat'] = $aNode[0];
        $this->pagedata['hasimage'] = $info['hasimage'];
        $this->pagedata['articles'] = $arr_articles;
        $this->set_tmpl('articlelist');
        $this->set_tmpl_file($info['list_tmpl_path']);
        if($info['hasimage']=='true'){
            $view = 'wap/article/list_image.html';
        }else{
           $view = 'wap/article/list.html';
        }
        if(is_null($this->weixin_share_page)) $this->weixin_share_page = array();
        if(in_array('article-list', $this->weixin_share_page)){
            $this->pagedata['from_weixin'] = $this->from_weixin;
            $this->pagedata['weixin']['appid'] = $this->weixin_a_appid;
            $this->pagedata['weixin']['imgUrl'] = base_storager::image_path(app::get('weixin')->getConf('weixin_basic_setting.weixin_logo'));
            $this->pagedata['weixin']['linelink'] = app::get('wap')->router()->gen_url(array('app'=>'content','ctl'=>'wap_article','act'=>'l','arg0'=>$art_list_id, 'full'=>1));
            $this->pagedata['weixin']['shareTitle'] = $this->pagedata['title'];
            $this->pagedata['weixin']['descContent'] = $this->pagedata['description'];
        }
        $this->page($view);
    }

    public function i/*nodeindex*/() {
         $nav_url = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:$this->gen_url(array('app'=>'wap', 'ctl'=>'default','act'=>'index'));
        $this->begin($nav_url);
        $id = $this->_request->get_param(0);
        if($id > 0) {
            $oCAN = kernel::single('content_article_node');
            $aPath = $oCAN->get_node_path($id, true);
            $GLOBALS['runtime']['path'] = $aPath;

            $info = $oCAN->get_node($id, true);
            if($info['ifpub']=='true') {
                $this->get_seo_info($info, $aPath);
                $this->set_tmpl_file($info['tmpl_path']);
                $this->page('content_node:' . $info['node_id']);
            } else {
                $this->end(false, app::get('content')->_('未发布！错误访问!'));
            }
        } else {
            $this->end(false, app::get('content')->_('错误访问!'));
        }
    }


    private function get_seo_info($aInfo, $aPath) {
        is_array($info) or $info = array();
        is_array($aPath) or $aPath = array();
        //title keywords description
        $title = array();
        $title[] = $aInfo['seo_title'] ? $aInfo['seo_title'] : $aPath[count($aPath)-1]['title'];
        if(!$aInfo['seo_title']) {
            $title[] = $this->site_name ? $this->site_name : app::get('site')->getConf('site.name');
        }
        $title = array_filter($title);

        $this->pagedata['title'] = implode('-', $title);
        $this->pagedata['description']  = $aInfo['seo_description'] ? $aInfo['seo_description'] : $this->pagedata['title'];
        if($aInfo['seo_keywords']) {
            $this->pagedata['keywords'] = $aInfo['seo_keywords'];
        } else {
            $keyword = array();
            foreach($aPath as $row) {
                $keyword[] = $row['title'];
            }
            $this->pagedata['keywords'] = implode('-', $keyword);
        }
    }

    function ajax_widgets(){
        $indexs = $this->app->model('article_indexs')->getList('article_id,title',array('node_id'=>intval($_POST['node_id'])) );
        echo json_encode($indexs);
    }

}//End Class
