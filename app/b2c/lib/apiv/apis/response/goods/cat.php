<?php
class b2c_apiv_apis_response_goods_cat{

	public $app;
    /**
     * 构造方法
     * @param object app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

     /**
     * 商品分类,按上级分类ID获取下级分类列表
     * @param $parent_id
     */
    public function get_cat_list($param,&$service){
        if(empty($param['cat_id'])){
            $param['cat_id'] = 0;
        }
    	$goods_cat=$this->app->model('goods_cat');
        $goods_cat_list=$goods_cat->getList('parent_id,cat_id,cat_name,is_leaf,type_id,last_modify',array('parent_id'=>$param['cat_id']));
        foreach( $goods_cat_list as $key=>$row ){
            $return[$key]['parent_id'] = $row['parent_id'];
            $return[$key]['cat_id'] = $row['cat_id'];
            $return[$key]['cat_name'] = $row['cat_name'];
            $return[$key]['is_leaf'] = $row['is_leaf'];
            $return[$key]['type_id'] = $row['type_id'];
            $return[$key]['last_modify'] = $row['last_modify'];
        }
    	return $return;
    }
}
