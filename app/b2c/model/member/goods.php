<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_member_goods extends dbeav_model{

    //var $defaultOrder = array('goods_id', ' ASC');

    ###添加缺货登记
    function add_gnotify($member_id=null,$good_id,$product_id,$email,$cellphone){
       $goodsData = app::get('b2c')->model('goods')->getList('name,price,goods_id,image_default_id',array('goods_id'=>$goods_id));
       $sdf = array(
       'goods_id' =>$good_id,
       'member_id' =>$member_id,
       'product_id'=>$product_id,
       'goods_name'=>$goodsData[0]['name'],
       'goods_price'=>$goodsData[0]['price'],
       'image_default_id'=>$goodsData[0]['image_default_id'],
       'email' => $email,
       'cellphone' => $cellphone,
       'status' =>'ready',
       'create_time' => time(),
       'type' =>'sto',
      );
      if($this->save($sdf)){
          return true;
      }
      else{
          return false;
      }
}

//检查邮箱重复登记货品

    function check_gnotify($aData){
        $goods_id = $aData['item'][0]['goods_id'];
        $product_id = $aData['item'][0]['product_id'];
        $email = $aData['email'];
        $aData = $this->getList('gnotify_id',array('goods_id' => $goods_id,'product_id' => $product_id,'email' => $email));
        if(count($aData)>0){
            return true;
        }
        else{
            return false;
        }
    }


#####根据会员ID获得缺货登记
    function get_gnotify($member_id,$member_lv_id,$page=1){
        $obj_prod = $this->app->model('products');
        $obj_good = $this->app->model('goods');
        $oGoodsLv = $this->app->model('goods_lv_price');
        $oMlv = $this->app->model('member_lv');
        $mlv = $oMlv->db_dump( $member_lv_id,'dis_count' );

        $count = $this->count(array('member_id' => $member_id,'type' =>'sto','object_type'=>'goods'),'sto');
        $maxPage = ceil($count / 10);
        if($page > $maxPage) $page = $maxPage;
        $start = ($page-1) * 10;
        $start = $start<0 ? 0 : $start;
        $aGid = $this->getList('*',array('member_id' => $member_id,'type' =>'sto','object_type' => 'goods'));
        $params['page'] = $maxPage;

        foreach($aGid as $val){
            $image = $obj_good->getList('*',array('goods_id'=>$val['goods_id'],'goods_type'=>'normal'));
            if(!$image){
                $Pro[] = $val;
            }else{
                $aTmp = $obj_prod->dump($val['product_id']);
                if(!$aTmp) $aTmp['product_id'] = $val['product_id'];
                if($member_lv_id){
                    $row = $oGoodsLv->getList( 'price',array('product_id'=>$val['product_id'],'level_id'=> $member_lv_id ));
                    $aTmp['price']['price']['price'] = $row[0] ? $row[0]['price'] : $aTmp['price']['price']['price'] * $mlv['dis_count'];
                    $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                    if(!empty($promotion_price['price'])){
                        $aTmp['price']['price']['price'] = $promotion_price['price'];
                        $aTmp['price']['price']['show_button'] = $promotion_price['show_button'];
                        $aTmp['price']['price']['timebuy_over'] = $promotion_price['timebuy_over'];
                    }
                }
                if( $aTmp['store'] != '' && !$aTmp['nostore_sell'] )
                    $aTmp['store'] = $aTmp['store']- $aTmp['freez'];
                else
                    $aTmp['store'] = 10000;
                $aTmp['image_default_id'] =$image[0]['image_default_id'];
                $aTmp['marketable'] =$image[0]['marketable'];
                $Pro[] = $aTmp;
            }
        }
        $params['data'] = $Pro;
        return $params;
    }


#####添加商品收藏

    function add_fav($member_id=null,$object_type='goods',$goods_id=null){
        if(!$member_id || !$goods_id) return false;
        $filter['member_id'] = $member_id;
        $filter['goods_id'] = $goods_id;
        $filter['type'] = 'fav';
        if($row = $this->getList('gnotify_id',$filter))
            return true;
        $goodsData = app::get('b2c')->model('goods')->getList('name,price,goods_id,image_default_id',array('goods_id'=>$goods_id));
        $sdf = array(
           'goods_id' =>$goods_id,
           'member_id' =>$member_id,
           'goods_name'=>$goodsData[0]['name'],
           'goods_price'=>$goodsData[0]['price'],
           'image_default_id'=>$goodsData[0]['image_default_id'],
           'status' =>'ready',
           'create_time' => time(),
           'type' =>'fav',
           'object_type'=> $object_type,
          );
          if($this->save($sdf)){
              return true;
          }
          else{
              return false;
          }
	}

	function get_member_fav($member_id=null){
		if(!$member_id) return null;
		$oGood = $this->app->model('goods');
		$fav = $this->db->select("SELECT member_goods.`goods_id`
									FROM ".$this->table_name(1)." AS member_goods
									INNER JOIN ".$oGood->table_name(1)." AS goods ON member_goods.`goods_id`=goods.`goods_id`
									WHERE member_goods.`member_id`=".intval($member_id)." AND member_goods.`type`='fav' AND goods.`marketable`='true'");
        $result = implode(',',(array)array_map('current',$fav));
        if($result) $result = ','.$result;
        return $result;
	}

###删除收藏商品

     function delFav($member_id,$gid,&$page=null,$num=10){
        $is_delete = false;
		$is_delete = $this->delete(array('goods_id' => $gid,'member_id' => $member_id,'type' => 'fav'));
		/** 得到当前会员分页数 **/
		$count = $this->count(array('member_id'=>$member_id));
		$page = ceil($count / $num);

		return $is_delete;
     }

	 function count($filter=null,$type=null){
		if (!$filter || !$filter['member_id']) return 0;

		$oGood = $this->app->model('goods');
		$count = $this->db->selectrow("SELECT COUNT(member_goods.`goods_id`) AS num
									FROM ".$this->table_name(1)." AS member_goods
									INNER JOIN ".$oGood->table_name(1)." AS goods ON member_goods.`goods_id`=goods.`goods_id`
									WHERE member_goods.`member_id`=".intval($filter['member_id'])." AND member_goods.`type`='". $type ."' AND goods.`marketable`='true'");

		return $count['num'];
	 }

     function delAllFav($member_id){
        return $this->delete(array('member_id' => $member_id,'type' => 'fav'));
     }

####根据会员ID获得该会员收藏的商品

    function get_favorite($member_id,$member_lv_id,$page=1,$num=10){
        $count = $this->count(array('member_id'=>$member_id),'fav');
        if( !$num ) $num = 10;
        $maxPage = ceil($count / $num);
        if($page > $maxPage) $page=$maxPage;//return array();
        $start = ($page-1) * $num;
        $start = $start<0 ? 0 : $start;
        $aGid = $this->getList('*',array('member_id' => $member_id,'type' =>'fav'), $start, $num, $orderType='create_time DESC');
        $agid = array();
        foreach($aGid as $val){
            $agid[]= $val['goods_id'];
            $params['data'][$val['goods_id']] = $val;
        }

        if(is_array($agid)&&$agid){
            $oMlv = $this->app->model('member_lv');
            $mlv = $oMlv->select()->columns(array('dis_count'))->where('member_lv_id=?',$member_lv_id)->instance()->fetch_row();
            $oImage = app::get('image')->model('image');
            $objProduct = $this->app->model('products');
            $oGoodsLv = $this->app->model('goods_lv_price');
            $oGood = $this->app->model('goods');
            $aProduct = $oGood->getList('udfimg,thumbnail_pic,image_default_id,goods_id,price,name,type_id,nostore_sell,marketable',array('goods_id' => $agid));

            if($aProduct){
                foreach ($aProduct as &$val) {
                    // 判断图片是否存在
                    $image_default_id = $oImage->select()->columns(array('image_id'))
                        ->where('image_id=?',$val['image_default_id'])->instance()->fetch_one();
                    if (empty($image_default_id)) {
                        $val['image_default_id'] = '';
                    }
                    $thumbnail_pic = $oImage->select()->columns(array('image_id'))
                        ->where('image_id=?',$val['thumbnail_pic'])->instance()->fetch_one();
                    if (empty($thumbnail_pic)) {
                        $val['thumbnail_pic'] = '';
                    }

                    $temp = $objProduct->getList('product_id, spec_info, price, freez, store, goods_id',array('goods_id'=>$val['goods_id'],'marketable'=>'true'),$offset=0, $limit=-1,$orderType='price DESC');
                    if( $member_lv_id ){
                        //货品会员价
                        $tmpGoods = array();
                        $goodsLvPrice = $oGoodsLv->select()->columns('product_id,price')
                            ->where('goods_id=?',$val['goods_id'])
                            ->where('level_id=?',$member_lv_id)->instance()->fetch_all();
                        foreach($goodsLvPrice as $k => $v ){
                            $tmpGoods[$v['product_id']] = $v['price'];
                        }

                        foreach( $temp as &$tv ){
                            $tv['price'] = (isset( $tmpGoods[$tv['product_id']] )?$tmpGoods[$tv['product_id']]:( $mlv['dis_count']*$tv['price'] ));
                        }

                        $val['price'] = $tv['price'];
                        $promotion_price = kernel::single('b2c_goods_promotion_price')->process($val);
                        if(!empty($promotion_price['price'])){
                            $val['price'] = $promotion_price['price'];
                            $val['show_button'] = $promotion_price['show_button'];
                            $val['timebuy_over'] = $promotion_price['timebuy_over'];
                        }
                    }
                    $val['spec_desc_info'] = $temp;

                    $params['data'][$val['goods_id']] = $val;
                }
            }
            //$params['data'] = $aProduct;
            $params['data'] = array_filter($params['data']);
            $params['page'] = $maxPage;

            return $params;
        }else{
            return false;
        }
    }

    /**
     * get_goods
     * 获取到货记录
     *
     * @access public
     * @return int
     */
    public function get_goods($member_id=null){
        if(!$member_id) return 0;
        $obj_product = $this->app->model('products');
        $aProduct = $this->getList('product_id',array('member_id' => $member_id, 'type' => 'sto'));
        $i = 0;
        foreach((array)$aProduct as $key => $v){
            if(!$v['product_id']) continue;
            $row = $obj_product->getList('store',array('product_id' => $v['product_id']));
            if($row[0]['store']>0) $i++;
        }
        return $i;
    }

}
