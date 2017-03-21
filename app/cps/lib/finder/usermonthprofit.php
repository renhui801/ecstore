<?php
/**
 * cps_finder_usermonthprofit
 * 后台消息第三方类finder控制层类
 *
 * @uses
 * @package
 * @author gujun<gujun@shopex.cn>
 * @copyright 2003-2011 ShopEx
 * @license Commercial
 * @version $Id:cps_finder_usermonthprofit Jun 20, 2011  3:45:56 PM ever $
 */
class cps_finder_usermonthprofit {

    //app属性
    private $app = null;
    //render属性
    private $render = null;

    /**
     * 初始化构造方法
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
    public $column_confirm = '操作';
    //编辑按钮显示顺序
    public $column_confirm_order = COLUMN_IN_TAIL;

    /**
     * 定义列表项编辑按钮方法
     * @access public
     * @param array $row 行数据
     * @return string
     * @version 2 Jul 5, 2011
     */
    public function column_confirm($row) {
        $str = '';
        //未发放的记录显示发放按钮
        if ($row['state'] == '1' || $_GET['p'][0] == 1) {
            $str = '<a href="index.php?app=cps&ctl=admin_usermonthprofit&act=grant&p[0]=' . $row['ump_id'] . '">' . '发放' . '</a>';
        } else {
            $str = '-';
        }

        //编辑按扭HTML
        $strHtml = '<div class="u118_text">' . $str . '</div>';
        return $strHtml;
    }
}