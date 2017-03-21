<?php

class importexport_data_b2c_goods { 

    /**
     * 导出商品用type_id进行分组
     */
    public function set_group($col){
        return 'type_id';
    }

    //导出商品公共的固定字段
    private function get_comm_title(){
 
        $title = array(
            'bn'            => app::get('b2c')->_('bn:商品编号'),
            'ibn'           => app::get('b2c')->_('ibn:规格货号'),
            'is_default'    => app::get('b2c')->_('col:默认货品'),
            'cat_name'      => app::get('b2c')->_('col:分类'),
            'brand_name'    => app::get('b2c')->_('col:品牌'),
            'keywords'      => app::get('b2c')->_('keywords:商品关键字'),
            'mktprice'      => app::get('b2c')->_('col:市场价'), //product
            'cost'          => app::get('b2c')->_('col:成本价'),
            'price'         => app::get('b2c')->_('col:销售价'),
            'thumbnail_pic' => app::get('b2c')->_('col:缩略图'),
            'pic_name'      => app::get('b2c')->_('col:图片文件'),//图片文件名称
            'name'          => app::get('b2c')->_('col:商品名称'),
            'status'        => app::get('b2c')->_('col:上架'),
            'spec'          => app::get('b2c')->_('col:规格'),
            'store'         => app::get('b2c')->_('col:库存'),
            'description'   => app::get('b2c')->_('col:详细介绍'),
            'weight'        => app::get('b2c')->_('col:重量'),
            'unit'          => app::get('b2c')->_('col:单位'),
            'brief'         => app::get('b2c')->_('col:商品简介'),
        );

        //会员价格字段导出
        $memberLvModel = app::get('b2c')->model('member_lv');
        $memberLvData = $memberLvModel->getList('name,member_lv_id');
        foreach( (array)$memberLvData as $mlv ){
            //title['member_lv_price/1'] = 'price:普通会员';
            $title['member_lv_price/'.$mlv['member_lv_id']] = 'price:'.$mlv['name'];
        }

        return $title;
    }

    /**
     * 在商品导出标题基础上加上商品类型的扩展属性字段
     * @param array $title  导出标题数组
     * @return array $title
     */
    private function _title_props($title){
        $propsData = app::get('b2c')->model('goods_type_props')->getList('props_id,type_id,name,goods_p');
        foreach( (array)$propsData  as $props_row){
            $type_id = $props_row['type_id'];
            $goods_p = $props_row['goods_p'];
            $title[$type_id]['props/p_'.$goods_p] = 'props:'.$props_row['name'];
        }
        return $title;
    }

    /**
     * 商品类型详细参数
     * @param init $type_id 类型ID
     * @param array $typeParams 类型详细参数
     * @param array $title 导出标题数组
     */
    private function _title_params($type_id,$typeParams,$title){
        if(empty($typeParams)) return $title;
        foreach( (array)$typeParams as $paramGroup => $paramItem ){
            foreach( (array)$paramItem as $paramName => $paramValue ){
                $key = 'params/'.$paramGroup.'/'.$paramName;
                $title[$type_id][$key] = 'params:'.$paramGroup.'->'.$paramName;
            }
        }
        return $title; 
    }

    public function get_title( $title = array() ){
        $comm_title = $this->get_comm_title();
        $goodsTypeModel = app::get('b2c')->model('goods_type');
        $goodsTypeData = $goodsTypeModel->getList('type_id,name,params', array('type_id') );

        $tmp_comm_title = array();
        $title = array();
        foreach( (array)$goodsTypeData as $row ){
            $title_header['type_name'] = '*:'.$row['name'];
            $tmp_comm_title = array_merge($title_header, $comm_title); 
            $title[$row['type_id']] = $tmp_comm_title; 
            $title = $this->_title_params($row['type_id'],$row['params'],$title);
        }

        $title = $this->_title_props($title);

        return $this->title = $title;
    }

    public function get_content_row($row){

        if(!$this->title){
            $this->title = $this->get_title(); 
        }

        $productsData = app::get('b2c')->model('products')->getList('product_id,bn,is_default,price,cost,mktprice,store,marketable,spec_info,spec_desc,weight',array('goods_id'=>$row['goods_id'])); 

        $goodsData = $this->_get_goods_data($row);
        $data = array();
        //单规格
        if( count($productsData) == 1 && !$productsData[0]['spec_desc'] )
        {
            $goodsData['ibn']        = $productsData[0]['bn'];
            $goodsData['is_default'] = $productsData[0]['is_default'];
            $goodsData['price']      = $productsData[0]['price'];
            $goodsData['cost']       = $productsData[0]['cost'];
            $goodsData['mktprice']   = $productsData[0]['mktprice'];
            $goodsData['store']      = $productsData[0]['store'];
            $goodsData['status']     = ($productsData[0]['marketable']=='true') ? 'Y' : 'N';
            $goodsData['weight']     = $productsData[0]['weight'];
            $goodsData  = $this->_get_member_lv_price($productsData[0]['product_id'],$goodsData); 
            foreach( (array)$this->title[$row['type_id']] as $col=>$col_name){
                $data[0][$col] = $goodsData[$col] ? $goodsData[$col] : '';
            }
        } 
        //多规格
        else 
        {
            //多规格第一行商品数据
            foreach( (array)$this->title[$row['type_id']] as $col=>$col_name){
                $headerGoods[$col] = $goodsData[$col] ? $goodsData[$col] : '';
            }
            $data[] = $headerGoods; 

            //多规格货品数据
            foreach( $productsData as $product_row)
            {
                $productData['type_name']  = $goodsData['type_name'];
                $productData['name']       = $goodsData['name'];
                $productData['bn']         = $goodsData['bn'];
                $productData['ibn']        = $product_row['bn'];
                $productData['is_default'] = $product_row['is_default'];
                $productData['price']      = $product_row['price'];
                $productData['cost']       = $product_row['cost'];
                $productData['mktprice']   = $product_row['mktprice'];
                $productData['store']      = $product_row['store'];
                $productData['weight']     = $product_row['weight'];
                $productData['status']     = ($product_row['marketable']=='true') ? 'Y' : 'N';
                //货品规格
                $productData['spec'] = $this->_get_product_spec_name($product_row['spec_info']);
                //会员价格
                $productData  = $this->_get_member_lv_price($product_row['product_id'],$productData); 

                //数组按照title排序
                foreach( (array)$this->title[$row['type_id']] as $col=>$col_name){
                    $content[$col] = $productData[$col] ? $productData[$col] : '';
                }
                $data[] = $content;
            }//end
        }
        return $data; 
    }

    /**
     * 组织商品数据
     * @param array $row 一条商品基本数据
     * @return array
     */
    private function _get_goods_data($row){
        $goodsData['bn'] = $row['bn'];
        $goodsData['name'] = $row['name'];
        $goodsData['store'] = $row['store'];
        $goodsData['brief'] = $row['brief'];
        $goodsData['description'] = str_replace( '"','""', str_replace("\n"," ",$row['intro']) );
        $goodsData['unit'] = $row['unit'];
        $goodsData['status'] = ($row['marketable'] == 'true') ? 'Y' : 'N';

        //缩略图
        $goodsData['thumbnail_pic'] = empty($row['thumbnail_pic']) ? $row['image_default_id'] : $row['thumbnail_pic'];
        if( $row['udfimg'] == 'true' && $goodsData['thumbnail_pic'] && substr($goodsData['thumbnail_pic'],0,4 ) != 'http' ){
            $oImage = app::get('image')->model('image');
            $imageData = $oImage->dump($goodsData['thumbnail_pic'],'url');
            $goodsData['thumbnail_pic'] = $goodsData['thumbnail_pic'].'@'.$imageData['url'];
        }

        //图片名称
        $goodsData['pic_name'] = $this->_get_gods_images($row['goods_id']);

        //类型名称
        $goodsTypeModel = app::get('b2c')->model('goods_type');
        $goodsTypeData = $goodsTypeModel->getList('type_id,name,params', array('type_id'=>$row['type_id']) );
        $goodsData['type_name'] = $goodsTypeData[0]['name'];

        //分类
        $catDataRow = app::get('b2c')->model('goods_cat')->getRow('cat_id',array('cat_name'=>$row['cat_id']));
        $goodsData['cat_name'] = $this->_get_cat_name($catDataRow['cat_id']);

        //品牌
        $goodsData['brand_name'] = $row['brand_id'];

        //商品关键字
        $goodsData['keywords'] = $this->_get_keywords($row['goods_id']);

        //商品规格
        $goodsData['spec'] = $this->_get_goods_spec_name($row['spec_desc']);

        //商品扩展属性
        $goodsData = $this->_get_goods_props($goodsData,$row);

        //商品详细参数
        $goodsData = $this->_get_goods_params($goodsData,$row);

        return $goodsData; 
    }

    /**
     * 导出商品中商品类型的详细参数
     * @param array $goodsData 处理后的导出商品数据
     * @param array $row 数据库中取出的商品数据 未处理 
     */
    private function _get_goods_params($goodsData,$row){
        foreach( (array)$row['params'] as $paramGroup => $paramItem ){
            foreach( (array)$paramItem as $paramName => $paramValue ){
                $key = 'params/'.$paramGroup.'/'.$paramName;
                $goodsData[$key] = $paramValue;
            }
        }
        return $goodsData;
    }

    /**
     * 导出商品类型的扩展属性数据
     * @param array $goodsData 处理后的导出商品数据
     * @param array $row 数据库中取出的商品数据 未处理
     */
    private function _get_goods_props($goodsData,$row){
        $propsValueModel = app::get('b2c')->model('goods_type_props_value');
        for ($i=1;$i<=50;$i++)
        {
            //1-20 select 21-50 input
            if ($row['p_'.$i] ){
                $propsValueId = $row['p_'.$i];
                if( $i <= 20){
                    $propsValueIds[] = $propsValueId;
                    $p_id[$propsValueId] = $i;
                }else{
                    $goodsData['props/p_'.$i] = $propsValueId;
                }
            }
        }//end for

        $propsVauleData = $propsValueModel->getList('props_value_id,name',array('props_value_id'=>$propsValueIds));
        foreach($propsVauleData as $row){
            $key = 'props/p_'.$p_id[$row['props_value_id']];
            $goodsData[$key] = $row['name'];
        }
        return $goodsData;
    }

    //获取货品的会员价，以数据库中的数据为准，如果填写了会员价则导出，未填则不导出
    private function _get_member_lv_price($product_id,$goodsData){
        $lvPriceModel = app::get('b2c')->model('goods_lv_price'); 
        $productPriceData = $lvPriceModel->getList( 'level_id,price', array('product_id'=>$product_id) );
        foreach( (array)$productPriceData as $row){
            $goodsData['member_lv_price/'.$row['level_id']] = $row['price'];
        }
        return $goodsData; 
    }

    //图片文件名称
    private function _get_gods_images($goods_id){
        $goodsImages = app::get('image')->model('image_attach')->getList('attach_id,image_id',array('target_id'=>$goods_id, 'target_type'=>'goods') );
        if( !$goodsImages ) return '';

        $imageModel = app::get('image')->model('image');
        foreach( $goodsImages as $row){
            $imageData = $imageModel->dump($row['image_id'],'url');
            $pic_name_arr[] = $row['image_id'].'@'.$imageData['url'];
        }
        $pic_name = implode('#',$pic_name_arr);
        return $pic_name;
    }

    //商品的规格信息
    private function _get_goods_spec_name($spec_desc){

        if( empty($spec_desc) ) return '-'; 
        $specModel = app::get('b2c')->model('specification');
        foreach( (array)$spec_desc as $spec_id=>$spec_value){
            $spec_ids[] = $spec_id;
        }
        $specData = $specModel->getList('spec_name,spec_id',array('spec_id'=>$spec_ids));
        foreach( $specData as $spec_row ){
            $spec[$spec_row['spec_id']] = $spec_row['spec_name'];
        }

        foreach( (array)$spec_desc as $spec_id=>$spec_value){
            $return[] = $spec[$spec_id];
        }

        return  implode('|',$return);
    }

    //货品规格值
    private function _get_product_spec_name($spec_info){
        if ( !$spec_info ) return '-';
        $arr_spec_info = explode('、',$spec_info);
        $spec_name = array();
        $spec = array();
        foreach( $arr_spec_info as $spec_val ){
            $spec_name = explode('：',$spec_val);
            $spec[] = $spec_name[1];

        }
        return implode('|',$spec); 
    }

    //商品关键字
    private function _get_keywords($goods_id){
        $goods_keywords = app::get('b2c')->model('goods_keywords')->getList('keyword',array('goods_id'=>$goods_id));
        $str_goods_keywords = '';
        $arr_goods_keywords = array();
        if($goods_keywords){
            foreach($goods_keywords as $keywords_k => $keywords_v){
                $arr_goods_keywords[$keywords_k] =  $keywords_v['keyword'];
            }
            $str_goods_keywords = implode('|',$arr_goods_keywords);
        }
        return $str_goods_keywords;
    }

    //cat_id 获取到cat_name
    private function _get_cat_name($cat_id){
        if($cat_id){
            $catModel = app::get('b2c')->model('goods_cat');
            $catData = $catModel->getList('cat_path', array('cat_id'=>$cat_id) );
            foreach( explode(',',$catData[0]['cat_path']) as $catVal ){
                if( $catVal )$catPath[] = $catVal;
            }
            $catPath[] = $cat_id;
            foreach( $catModel->getList('cat_name',array('cat_id'=>$catPath)) as $acat ){
                if( $acat ) $cat[] = $acat['cat_name'];
            }
            $cat_name = implode('->',$cat);
        }
        return $cat_name ? $cat_name : '';
    }


    /*-----------------------以下为导入函数-----------------------*/

    /**
     * 如果是最后一条记录和倒数第二天记录属于同一个商品则继续下去
     * @return bool true 继续获取下一行 false 不需要在获取,已经完整的获取到一条商品数据
     */
    public function check_continue(&$contents,$line){

        if ( count($contents) == 1 || ($contents[$line]['bn'] && $contents[$line-1]['bn'] && $contents[$line]['bn'] == $contents[$line-1]['bn']) )
        {
            return true;
        } 
        else
        {
            array_pop($contents);
            return false;
        }
    }

    /**
     *将导入的数据转换为sdf
     *
     * @param array $contents 导入的一条商品数据
     * @param string $msg 传引用传出错误信息
     */
    public function dataToSdf($contents, &$msg){

        $goodsModel = app::get('b2c')->model('goods');

        $goods = current($contents);

        if( !$goods['bn'] ){
            $msg['error'] = app::get('importexport')->_('商品名称为：').$goods['name'].app::get('importexport')->_(' 的商品编号必填');
            return false;
        }

        if( $this->bn && in_array($goods['bn'],$this->bn) ){
            $msg['error'] = app::get('importexport')->_('商品编号重复：').$goods['bn'];
            return false;
        }

        //商品名称不能为空
        if(!$goods['name']){
            $msg['error'] = app::get('importexport')->_('商品编号为：').$goods['bn'].app::get('importexport')->_('的商品名称不能为空');
            return false;
        }

        $old_goods_spec = array();
        //如果goods_id 已存在则直接赋值goods_id update数据
        if( $goods['bn'] && $goodsRow = $goodsModel->getList('goods_id,spec_desc', array('bn|tequal'=>$goods['bn'])) ){
            $goods_id  = $goodsRow[0]['goods_id'];
            $old_goods_spec = $goodsRow[0]['spec_desc'];
        }

        if($goods['brief']&&strlen($goods['brief'])>210){
            //简短的商品介绍,请不要超过70个字！
            $msg['error'] = app::get('importexport')->_('商品编号为'.$goods['bn'].'的商品介绍,请不要超过70个字!');
            return false;
        }
        
        //获取到商品类型ID 没有则不是最新数据
        if (!empty($goods['type_name']) ){
            $type = app::get('b2c')->model('goods_type')->getList('type_id',array('name'=>$goods['type_name']));
            if( $type ){
                $type_id = $type[0]['type_id'];
            }else{
                $msg['error'] = app::get('importexport')->_('商品编号为'.$goods['bn'].'的类型未填');
                return false;
            }
        }else{
            $msg['warning'] .= app::get('importexport')->_(' 商品编号为'.$goods['bn'].'的类型未填');
        }

        //获取到商品品牌ID  没有则不是最新数据
        if(!empty($goods['brand_name'])){
            $brand_id = app::get('b2c')->model('brand')->getList("brand_id",array('brand_name'=>trim($goods['brand_name'])));
            if( $brand_id ){
                $brand_id = $brand_id[0]['brand_id'];
            }else{
                $msg['error'] = app::get('importexport')->_($goods['brand_name'].'品牌不存在');
                return false;
            }
        }else{
            $msg['warning'] .= app::get('importexport')->_(' 商品编号为'.$goods['bn'].'的品牌未填');
        }

        $cat_id = $this->_get_cat_id($goods['cat_name']);

        //处理商品关键字
        if( $goods['keywords'] ){
            foreach( explode( '|', $goods['keywords']) as $keyword ){
                $keywords[] = array(
                    'keyword' => $keyword,
                    'res_type' => 'goods'
                );
            }
        }

        //图片处理
        $imagesData = $this->_import_images($goods);

        if( count($contents) == 1){
            $productData = $this->_import_product_data($goods,$goods_id,$msg);
            $productData['is_default'] = 'true';
            if( $productData == false) return false;
            $store = $productData['store']; 
            $save_product[0] = $productData;
        }else{
            $goods_spec = $this->_import_spec($goods['spec'],$type_id);

            array_shift($contents);
            $products_is_default = null;
            foreach( $contents  as $product_row){
                $productData = $this->_import_product_data($product_row,$goods_id,$msg);
                if( $productData['is_default'] == 'true' ){
                    $products_is_default = 'true';
                }
                if( $productData == false) return false;
                $store += $productData['store']; 

                $productSpec = $this->_import_product_spec($product_row['spec'],$goods_spec,$msg);
                if( $productSpec == false){
                    $msg['error'] .= app::get('importexport')->_('对应的货品编号为').$product_row['ibn']; 
                    return false;
                } 
                $productData['spec_info'] = $productSpec['spec_info'];
                $productData['spec_desc'] = $productSpec['spec_desc'];

                $save_product[] = $productData;
            }

            if( !$products_is_default ){
                $save_product[0]['is_default'] = 'true';
            }

            foreach($goods_spec as $row){
                $spec[$row['spec_id']] = $row;
            }
        }

        $spec = $this->_import_merage_spec($old_goods_spec,$spec);

        $save_data = array(
            'category' => array('cat_id'=>$cat_id),
            'type'  => array('type_id'=>$type_id),
            'name' =>  $goods['name'],
            'bn'  => $goods['bn'],
            'brand' => array('brand_id'=>$brand_id),
            'brief' => $goods['brief'],
            'product' => $save_product,
            'images'  => $imagesData['images'],
            'thumbnail_pic'  => $imagesData['thumbnail_pic'],
            'image_default_id' => $imagesData['image_default_id'],
            'props'  => $this->_import_props($goods),
            'params' => $this->_import_params($goods),
            'spec'  => $spec,
            'store' => $store,
            'keywords' => $keywords,
            'unit' => $goods['unit'],
            'weight' => floatval($goods['weight']),
            'status' => ($goods['status'] == 'Y') ? 'true' : 'false',
            'description' => $goods['description']
        );

        $this->bn[] = $save_data['bn'];

        if($goods_id){
            $save_data['goods_id'] = $goods_id;
        }
        return $save_data;
    }

    //以后的规格数据合并导入的规格数据
    public function _import_merage_spec($old_goods_spec,$new_spec){
        if( !$old_goods_spec ) return $new_spec;

        foreach( (array)$old_goods_spec as $spec_id=>$row){
            foreach($row as $private=>$value){
                $spec_value_ids[] = $value['spec_value_id'];
                $privates[$value['spec_value_id']] = $private;
            }
        }

        $data = $new_spec;//
        foreach($new_spec as $spec_id=>$row){
            $data[$spec_id]['option'] = $old_goods_spec[$spec_id];
            foreach($row['option'] as $private=>$value){
                $data[$spec_id]['option'][$private] = $value;
                if( in_array($value['spec_value_id'],$spec_value_ids) ){
                    $oldPrivates = $privates[$value['spec_value_id']];
					unset( $data[$spec_id]['option'][$oldPrivates] );
                }
            }
        }
        return $data;
    }

    //导入扩展属性
    public function _import_props($goods){
        $props = array();
        foreach( $goods as $col=>$value){
            //处理商品扩展属性信息
            if( substr($col,0,5) == 'props' ){
                $goods_p = substr($col,(strrpos($col,'_')+1));
                if( $goods_p <= 20 ) {
                    $propsData = app::get('b2c')->model('goods_type_props_value')->getList('props_value_id',array('name'=>$value));
                    $props['p_'.$goods_p] = array('value'=>$propsData[0]['props_value_id']);
                }else{
                    $props['p_'.$goods_p] = array('value'=>$value);
                }
            }
        }//end function 
        return $props;
    }

    //导入params
    public function _import_params($row){
        $goodsParams = array();
        foreach((array)$row as $col=>$val){
            if( substr($col,0,6) == 'params' ){
                $arr = explode('/',$col);
                $paramGroup = $arr[1];
                $paramName  = $arr[2];
                $goodsParams[$paramGroup][$paramName] = $val;
            }
        }
        return $goodsParams;
    }

    //确定导入规格的id
    public function _import_spec($spec,$type_id)
    {
        $goodsTypeSpec = app::get('b2c')->model('goods_type_spec')->getList('spec_id',array('type_id'=>$type_id)); 
        foreach( $goodsTypeSpec as $row){
            $specIds[] = $row['spec_id']; 
        }

        $specification = app::get('b2c')->model('specification')->getList('spec_id,spec_name,spec_type',array('spec_id'=>$specIds)); 
        foreach((array) $specification as $row){
            $tmpspec[$row['spec_name']]['spec_name'] = $row['spec_name'];
            $tmpspec[$row['spec_name']]['spec_id'] = $row['spec_id'];
            $tmpspec[$row['spec_name']]['spec_type'] = $row['spec_type'];
        }

        foreach( explode('|',$spec) as  $k=>$specName ){
            $data[$k] = $tmpspec[$specName]; 
        }
        return $data;
    }

    //获取货品规格
    public function _import_product_spec($spec,&$goods_spec,&$msg)
    {
        $specValuesModel = app::get('b2c')->model('spec_values');
        foreach( explode('|',$spec) as  $k=>$specv ){
            $spec_id = $goods_spec[$k]['spec_id'];
            $specValuesData = $specValuesModel->getList('*',array('spec_value'=>$specv,'spec_id'=>$spec_id));
            if(!$specValuesData){
                $msg['error'] = app::get('importexport')->_('规格值：').$specv.app::get('importexport')->_('不存在');
                return false;
            }
            $private = time().$specValuesData[0]['spec_value_id'];
            $goods_spec[$k]['option'][$private] = array(
                'private_spec_value_id' => $private,
                'spec_value' =>$specv,
                'spec_value_id' => $specValuesData[0]['spec_value_id'],
                'spec_image' => $specValuesData[0]['spec_image'],
                'spec_goods_images' => '',
            );
            $spec_info[] = $goods_spec[$k]['spec_name'].'：'.$specv;
            $spec_desc['spec_value'][$spec_id] = $specv; 
            $spec_desc['spec_private_value_id'][$spec_id] = $private; 
            $spec_desc['spec_value_id'][$spec_id] = $specValuesData[0]['spec_value_id']; 
        }

        $return['spec_desc'] = $spec_desc;
        $return['spec_info'] = implode('、',$spec_info);
        return $return;
    }

    //货品数据
    public function _import_product_data($product_row,$goods_id,&$msg){
        if( $product_row['ibn'] ){
            $product['bn']= $product_row['ibn'];
            $proId = app::get('b2c')->model('products')->dump( array('bn|tequal'=>$product['bn']),'product_id,goods_id' );
        }else{
            $msg['error'] = app::get('importexport')->_('商品编码为：').$product_row['bn'].app::get('importexport')->_('的货品编号必填');
            return false;
        }

        if($proId['product_id']){
            if( ( !$goods_id && $proId['product_id'] ) || ( $goods_id && $goods_id != $proId['goods_id'] ) ){
                $msg['error'] = app::get('importexport')->_('货品编号：').$product['bn'].app::get('importexport')->_('已存在');
                return false;
            }
            $product['product_id'] = $proId['product_id'];
        }

        if( $this->product_bn && in_array($product['bn'],$this->product_bn) ){
            $msg['error'] = app::get('importexport')->_('货品编号：').$product['bn'].app::get('importexport')->_('重复');
            return false;
        }else{
            $this->product_bn[] = $product['bn'];
        }

        $product['status'] = ($product_row['status'] == 'Y') ? 'true' : 'false';
        $product['weight'] = floatval($product_row['weight']);
        $product['store'] = $product_row['store'];
        $product['unit'] = $product_row['unit'];
        $product['is_default'] = (strtolower($product_row['is_default']) == 'true') ? $product_row['is_default'] : 'false';

        foreach ( $product_row as $col=>$value){
            //会员价
            if( $value && substr($col,0,15) == 'member_lv_price' ){
                $level_id = substr($col,(strrpos($col,'/')+1));
                $member_lv_price[] = array(
                    'level_id'=>$level_id,
                    'price'=>floatval($value),
                );
            }
        }

        $product['price'] = array(
            'price' =>array('price'=>floatval($product_row['price'])),
            'member_lv_price' =>$member_lv_price,
            'cost' =>array('price'=>floatval($product_row['cost'])),
            'mktprice'=>array('price'=>floatval($product_row['mktprice']) ),
        );

        if($goods_id){
            $product['goods_id'] = $goods_id;
        }
        return $product;
    }

    function _import_images($goods){
        if( $goods['thumbnail_pic'] ){
            $oImage = app::get('image')->model('image');

            $image = explode('@',$goods['thumbnail_pic'] );
            if( count($image) == 2 ){
                $imageId = $image[0];
                $image = $image[1];
                $return['udfimg'] = 'true';
            }else{
                $imageId = null;
                $image = $image[0];
            }
            if( substr($image,0,4 ) == 'http' ){
                $imageName = null;
            }else{
                $imageName = null;
                $image = ROOT_DIR.'/'.$image;
            }
            if( $imageId && !$oImage->dump($imageId) )
                $imageId = null;
            $imageId = $oImage->store($image,$imageId,null,$imageName);

            $return['thumbnail_pic'] = $imageId;
        }

        if( $goods['pic_name'] ){
            $oImage = app::get('image')->model('image');
            $i = 0;
            foreach( explode( '#', $goods['pic_name'] ) as $image ){
                $image = explode('@',$image);
                if( count($image) == 2 ){
                    $imageId = $image[0];
                    $image = $image[1];
                }else{
                    $imageId = null;
                    $image = $image[0];
                }
                if( substr($image,0,4 ) == 'http' ){
                    $imageName = null;
                }else{
                    $imageName = null;
                    $image = ROOT_DIR.'/'.$image;
                }
                if( $imageId && !$oImage->dump($imageId) )
                    $imageId = null;
                $imageId = $oImage->store($image,$imageId,null,$imageName);

                //商品批量上传图片大中小的处理
                $oImage->rebuild($imageId,array('L','M','S'));

                $return['images'][] = array(
                    'target_type'=>'goods',
                    'image_id'=>$imageId
                );
                if( $i++ == 0 ){
                    $return['image_default_id'] = $imageId;
                }
            }
        }

        return $return;
    }

    private function _get_cat_id($cat_path){
        if(empty($cat_path) || $cat_path == '->'){
            $cat_id = 0;
        }else{
            $cat_id = 0;
            foreach( explode( '->',$cat_path ) as $cat_name ){
                $cat = app::get('b2c')->model('goods_cat')->dump(array('cat_name'=>$cat_name,'parent_id'=>$cat_id),'cat_id');
                if( $cat ){
                    $cat_id = $cat['cat_id'];
                }
            }
        }
        return $cat_id;
    }
}
        $data = $new_spec; 
