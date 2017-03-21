<?php 
	
	function theme_widget_cfg_sales_rank(){
		
		$data['goods_order_by'] = b2c_widgets::load('Goods')->getGoodsOrderBy();

		return $data;
	}
?>
