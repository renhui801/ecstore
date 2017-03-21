<?php
class b2c_goods_description_selllog{
    function __construct( &$app ) {
        $this->app = $app;
    }

    function show( $gid, &$aGoods=null, $custom_view=""){
        $render = $this->app->render();
        if( !$aGoods ){
            $objProduct = $this->app->model('products');
            $sellLogList = $objProduct->getGoodsSellLogList($gid,0,$this->app->getConf('selllog.display.listnum'));
            $sellLogSetting['display'] = array(
                'switch'=>$this->app->getConf('selllog.display.switch') ,
                'limit'=>$this->app->getConf('selllog.display.limit') ,
                'listnum'=>$this->app->getConf('selllog.display.listnum')
            );
            $render->pagedata['sellLog'] = $sellLogSetting;
            $render->pagedata['sellLogList'] = $sellLogList;
        }
        $aGoods['goods_id'] = $gid;

        $render->pagedata['goods'] = $aGoods;
		$file = $custom_view?$custom_view:"site/product/description/sellloglist.html";
		if($custom_view){
			return $render->fetch($file,'',true);
        }
        return $render->fetch($file);
    }

}

