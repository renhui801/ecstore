<?php 
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 *
 *
 * @package default
 * @author kxgsy163@163.com
 */
class groupactivity_mdl_orders extends b2c_mdl_orders
{
    private $group_order_obj = '';
    
    
    function __construct($app) {
        $this->group_order_obj = $app->model('order_act');
        parent::__construct($app);
    }
    
    public function get_schema(){
        $this->app = app::get('b2c');
        $columns = parent::get_schema();
        foreach( array('ship_status') as $key ) {
            if( in_array($key,$columns['in_list']) ) {
                unset($columns['in_list'][array_search($key,$columns['in_list'])]);
            }
        }
        return $columns;
    }
    
    public function table_name($real=false){
        $app_id = $this->app->app_id;
        $table_name = substr(get_parent_class($this),strlen($app_id)+5);
        if($real){
            return kernel::database()->prefix.$this->app->app_id.'_'.$table_name;
        }else{
            return $table_name;
        }
    }
    
    
    
    /*
     * 用于finder
     */
    public function count_finder( $filter )
    {
        $this->_get_filter( $filter );
        return $this->count( $filter );
    }
    #End Func
    
    
    /*
     * 用于finder
     */
    public function get_list_finder($cols='*', $filter=array(), $offset=0, $limit=-1, $orderType=null)
    {
        #$arr = $this->group_order_obj->getList( '*',array() );
        $this->_get_filter( $filter );
        
        return $this->getList( $cols,$filter,$offset,$limit,$orderType );
    }
    #End Func
    
    
    private function _get_filter( &$filter ) {
        $tmp = array();
        $tmp['disabled'] = 'false';
        if( $filter['order_id'] )
        {
            $tmp['order_id|has'] = $filter['order_id'];
        }
        
        $arr = $this->group_order_obj->getList( 'order_id',$tmp );
        $filter['order_id'] = array_map('current',$arr);
            
        if( !$filter ) {
            $filter = array();
            $filter['order_refer'] = 'local_group';
        }
    }
}