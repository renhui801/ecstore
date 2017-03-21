<?php

    function theme_widget_cfg_index_tab_goods(){

        $data['goods_order_by'] = b2c_widgets::load('Goods')->getGoodsOrderBy();

        return $data;
    }
?>
