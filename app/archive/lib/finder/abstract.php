<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * 这个类实现报表的数据统计和显示的抽象类
 * @abstract implements ectools_analysis_interface
 * @auther shopex ecstore dev dev@shopex.cn
 * @version 0.1
 * @package ectools.lib.analysis
 */
abstract class archive_finder_abstract
{

    /**
     * @var protected params array
     */
    protected $_params = null;
    /**
     * @var protected render object
     */
    protected $_render = null;
    /**
     * @var protected extra view
     */
    protected $_extra_view = null;
    /**
     * @var public extra search info array
     */
    // public $extra_search_info = array();

    /**
     * @var public finder options array
     */
    public $finder_options = array(
        'hidden' => false,
    );
    
    /**
     * 构造方法
     * @param object app
     * @return null
     */
    function __construct(&$app) 
    {
        $this->app = $app;
        if(substr(PHP_SAPI_NAME(),0,3) !== 'cli' && base_rpc_service::$is_start != true) {
            $this->_render = kernel::single('desktop_controller');
        }
        $this->_params = array();
        $this->_extra_view = array('archive' => 'finder/extra_view.html');
    }//End Function

    /**
     * 设置报表统计的参数
     * @param array 需要设置的参数
     * @return object 本类对象
     */
    public function set_params($params) 
    {
        $this->_params = $params;

        // 上个季度的月份开始时间
        $preSeasonAfter = mktime(0, 0, 0, date('n')-3 , 1, date('Y'));
        // 计算得出的所在年
        $year = date('Y', $preSeasonAfter);
        // 计算得出的所在月份
        $month = date('n', $preSeasonAfter);
        // 计算得出的月份的季度开始月份
        $startMonth = intval(($month - 1)/3)*3 + 1;
        // 计算得出的月份的季度结束月份
        $endMonth = $startMonth + 2;
        // 计算得出的季度的开始时间
        $time_from = date('Y-m-1', strtotime("{$year}-{$startMonth}-1"));
        // 计算得出的季度的最大时间
        $time_to = date('Y-m-t', strtotime("{$year}-{$endMonth}-1"));

        $this->_params['time_from'] = ($this->_params['time_from']) ? $this->_params['time_from'] : $time_from;
        $this->_params['time_to'] = ($this->_params['time_to']) ? $this->_params['time_to'] : $time_to;

        return $this;
    }//End Function


    /**
     * 设置extra视图
     * @param array view视图数组
     * @return object 本类对象
     */
    public function set_extra_view($array) 
    {
        $this->_extra_view = $array;
        return $this;
    }//End Function

    /**
     * 生成各自统计内容的finder
     * @param null
     * @return array - finder统一格式的数组
     */
    public function finder() 
    {
        //todo:各自实现
    }//End Function

    /**
     * 生成头部信息
     * @param null
     * @return null
     */
    public function headers() 
    {
        $this->_render->pagedata['time_from'] = $this->_params['time_from'];
        $this->_render->pagedata['time_to'] = $this->_params['time_to'];

        $this->_render->pagedata['month'] = array(1,2,3,4,5,6,7,8,9,10,11,12);
        $this->_render->pagedata['from_month'] = array(1,4,7,10);
        $this->_render->pagedata['to_month']   = array(3,6,9,12);

        for($i = 2000;$i<=date("Y",time());$i++){
            $year[] = $i;
        }
        $this->_render->pagedata['year'] = $year;
        $this->_render->pagedata['from_selected'] = explode('-',$this->_params['time_from']);
        $this->_render->pagedata['to_selected'] = explode('-',$this->_params['time_to']);
        $finder = $this->finder();
        $this->_render->pagedata['extra_search_info'] = kernel::single($finder['model'])->extra_search_info();
    }//End Function
    
   /**
    * 展示页面内容的方法
    * @param boolean true - 提出内容，相当于fetch，false echo内容
    * @return string html结果内容
    */
    public function display($fetch=false) 
    {
        $this->headers();
        if($this->finder_options['hidden']){
            foreach($this->_extra_view AS $app_id=>$view){
                $content = $this->_render->fetch($view, $app_id);
                break;
            }
        }else{
            $finder = $this->finder();
            $finder['params']['base_filter'] = $this->_params;
            $finder['params']['base_filter']['top_extra_view'] = true; //后台自定义搜索时，增加一个条件的判断，是否用新的搜索条件
            $finder['params']['top_extra_view'] = $this->_extra_view;
            ob_start();
            $this->_render->finder($finder['model'], $finder['params']);
            $content = ob_get_contents();
            ob_end_clean();
        }

        if($fetch){
            return $content;
        }else{
            echo $content;
        }
    }//End Function
    
    /**
     * fetch 页面的html
     * @param null
     * @return string html页面nei'ron
     */
    public function fetch() 
    {
        return $this->display(true);
    }//End Function


}//End Function
