<?php
class weixin_ctl_admin_setting extends desktop_controller{

    var $workground = 'wap.workground.theme';

    /*
     * @param object $app
     */
    function __construct($app)
    {
        parent::__construct($app);
        $this->ui = new base_component_ui($this);
        $this->app = $app;
        header("cache-control: no-store, no-cache, must-revalidate");

    }//End Function

    public function index(){
        $setting['weixin_logo']  = app::get('weixin')->getConf('weixin_basic_setting.weixin_logo');
        $setting['weixin_name']  = app::get('weixin')->getConf('weixin_basic_setting.weixin_name');
        $setting['weixin_brief'] = app::get('weixin')->getConf('weixin_basic_setting.weixin_brief');
        $setting['share_page'] = app::get('weixin')->getConf('weixin_basic_setting.share_page');
        $share_page = array(
            'index'=>app::get('b2c')->_('首页'),
            'gallery-index'=>app::get('b2c')->_('商品列表页'),
            'product-index'=>app::get('b2c')->_('商品详情页'),
            'article-index'=>app::get('b2c')->_('文章页'),
            'article-list'=>app::get('b2c')->_('文章列表页'),
        );
        $this->pagedata['setting'] = $setting;
        $this->pagedata['share_page'] = $share_page;
        $this->pagedata['select_share_page'] = isset($setting['share_page']) ? array_flip($setting['share_page']) : array('index'=>'true');
        $this->page('admin/setting.html');
    }

    public function save_setting(){
        $this->begin();

        app::get('weixin')->setConf('weixin_basic_setting.weixin_logo', $_POST['weixin_logo']);
        app::get('weixin')->setConf('weixin_basic_setting.weixin_name', $_POST['weixin_name']);
        app::get('weixin')->setConf('weixin_basic_setting.weixin_brief', $_POST['weixin_brief']);
        app::get('weixin')->setConf('weixin_basic_setting.share_page', $_POST['share_page']);

        $this->end(true,app::get('weixin')->_('保存成功'));

    }

    public function link_view(){
        $article_nodes = kernel::single('content_article_node')->get_selectmaps();
        array_unshift($article_nodes, array('node_id'=>0, 'step'=>1, 'node_name'=>app::get('content')->_('---所有---')));
        $this->pagedata['article_nodes'] = $article_nodes;

        $auth_module = kernel::single('weixin_menus')->auth_module();
        $this->pagedata['auth_module'] = $auth_module;

        $this->display('editor/link.html');
    }

    public function download_qrcode(){
        $filename = $_GET['name'].'.png';
        header("Cache-Control: public");
        //header("Content-Type: application/force-download");
        header("Content-type: image/png");
        header("Accept-Ranges: bytes");
        if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            $iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
            header("Content-Disposition: attachment; filename=\"$iefilename\"");
        } else {
            header("Content-Disposition: attachment; filename=\"$filename\"");
        }
        $imageData = app::get('image')->model('image')->getRow('url',array('image_id'=>$_GET['image_id']));
        $url = kernel::base_url(1).'/'.$imageData['url'];
        echo file_get_contents($url);
        exit;
    }
}

