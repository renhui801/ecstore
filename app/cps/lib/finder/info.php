<?php
class cps_finder_info {

    //app属性
    private $app = null;
    //render属性
    private $render = null;

    /**
     * 初始化构造
     * @access public
     * @param object $app
     * @version 1 Jun 22, 2011 创建
     */
    public function __construct($app) {
        //初始化app属性
        $this->app = $app;
        //初始化render属性
        $this->render = $this->app->render();
    }

    //编辑按钮
    public $column_edit = '编辑';
    //编辑按钮顺序
    public $column_order = 1;

    /**
     * 定义列表项编辑按钮
     * @access public
     * @param array $row 行数据
     * @return string
     * @version 1 Jun 22, 2011 创建
     */
    public function column_edit($row) {
        //编辑按钮
        $strHtml = '<a href="index.php?app=cps&ctl=admin_info_detail&act=edit&infoId=' . $row['info_id'] . '">编辑</a>';
        return $strHtml;
    }

    //预览按钮
    public $column_preview = '预览';
    //预览按钮顺序
    public $column_preview_order = 2;

    /**
     * 定义列表项预览按钮
     * @access public
     * @param array $row 行数据
     * @return string
     * @version 1 Jun 22, 2011 创建
     */
    public function column_preview($row) {
        //预览按钮
        if($row['i_type']==1){
            $strHtml = '<a href="' . app::get('site')->router()->gen_url(array('app' => 'cps', 'ctl' => 'site_info', 'act' => 'index', 'arg0' => $row['info_id'])) . '" target="_blank">预览</a>';
        }elseif($row['i_type']==2){
            if($row['pubtime']>time()){
                $title='发布时间未到,不能预览！';
                $strHtml = '<label  title="' . $title.'" >预览</label>';
            }else{
                $previewUrl = app::get('site')->router()->gen_url(array('app' => 'cps', 'ctl' => 'site_info', 'act' => 'showHelp'));
                $strHtml = '<a title="预览" href="' . $previewUrl.'#faq'. $row['info_id'].'" target="_blank">预览</a>';
            }
        }
        return $strHtml;
    }
}