<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_mdl_products extends dbeav_model{
    var $has_many = array(
        'price/member_lv_price' => 'goods_lv_price:contrast',
        );


    function __construct($app){
        parent::__construct($app);
        //使用meta系统进行存储
        $this->use_meta();
    }

    function getRealStore( $pId ){

        $data = $this->dump($pId,'store,freez');

        if( $pId === null )
            return null;
        return $data['store'] - $data['freez'];
    }

    function checkStore($pId, $quantity){
        $realQuantity = $this->getRealStore($pId);
        if(!is_null($realQuantity)){
            if($realQuantity < $quantity){

                return false;
            }
        }
        return true;

    }

    function getRealMkt($price){
        if($this->app->getConf('site.show_mark_price')=='true'){
            $math = $this->app->getConf('site.market_price');
            $rate = $this->app->getConf('site.market_rate');
            if($math == 1)
               return $price = $price*$rate;
            if($math == 2)
               return $price = $price+$rate;
        }else{
            return $price;
        }
    }

    function save(&$data,$mustUpdate = null, $mustInsert=false){
        if (isset($data['spec_desc']) && $data['spec_desc'] && is_array($data['spec_desc']) && isset($data['spec_desc']['spec_value']) && $data['spec_desc']['spec_value'])
        {
            $oSpec = $this->app->model('specification');
            $tmpSpecInfo = array();
            foreach( $data['spec_desc']['spec_value'] as $spec_v_k => $spec_v_v ){
                $specname = $oSpec->dump( $spec_v_k,'spec_name' );
                $tmpSpecInfo[] = $specname['spec_name'].'：'.$spec_v_v;
            }
            $data['spec_info'] = implode('、', (array)$tmpSpecInfo);
        }
        if( $data['price']['member_lv_price'] )
            foreach( $data['price']['member_lv_price'] as $k => $v ){
                $data['price']['member_lv_price'][$k]['goods_id'] = $data['goods_id'];
            }

        $data['freez'] = intval($data['freez']);
        return parent::save($data,$mustUpdate);
    }

    function dump($filter,$field = '*',$subSdf = null){
        $data = parent::dump($filter,$field,$subSdf);
        if( !isset($this->site_member_lv_id ) ){
            $userObject = kernel::single('b2c_user_object');
            $siteMember = $userObject->get_members_data(array('members'=>'member_lv_id'));
            $this->site_member_lv_id = $siteMember['members']['member_lv_id'];
        }
        if (isset($data['price']) && $data['price'] && is_array($data['price']) && isset($data['price']['member_lv_price']) && $data['price']['member_lv_price'] && is_array($data['price']['member_lv_price']))
        {
            if( array_key_exists( 'member_lv_price', $data['price'] ) && array_key_exists( $this->site_member_lv_id, $data['price']['member_lv_price'] ) ){
                $data['price']['price']['current_price'] = $data['price']['member_lv_price'][$this->site_member_lv_id]['price'];
            }else{
                $data['price']['price']['current_price'] = $data['price']['price']['price'];
            }
        }
        return $data;
    }

    /**
     * 重写getlist方法
     */
    public function getList($cols='*',$filter=array(),$start=0,$limit=-1,$orderType=null){
        $arr_product = parent::getList($cols,$filter,$start,$limit,$orderType);
        $obj_extends_service = kernel::servicelist('b2c.api_goods_extend_actions');
        if ($obj_extends_service)
        {
            foreach ($obj_extends_service as $obj)
            {
                $obj->extend_get_product_list($arr_product);
            }
        }

        return $arr_product;
    }

    function _dump_depends_goods_lv_price(&$data,&$redata,$filter,$subSdfKey,$subSdfVal){
        $oMlv = $this->app->model('member_lv');
        $memLvId = $oMlv->getList('member_lv_id','',0,-1);
        foreach( $memLvId as $aMemLvId )
            $idArray[] = array( 'level_id'=>$aMemLvId['member_lv_id'],'product_id'=>$data['product_id'] );
        $subObj = $this->app->model('goods_lv_price');
        //$idArray = $subObj->getList( implode(',',(array)$subObj->idColumn), $filter,0,-1 );
        foreach( (array)$idArray as $aIdArray ){
            $subDump = $subObj->dump($aIdArray,$subSdfVal[0],$subSdfVal[1]);
            if( $this->has_many[$subSdfKey] ){
                switch( count($aIdArray) ){
                    case 1:
                        eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"][current($aIdArray)] = $subDump;');
                        break;
                    case 2:
                        eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"][current(array_diff_assoc($aIdArray,$filter))] = $subDump;');
                        break;
                    default:
                        eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"][] = $subDump;');
                        break;
                }
            }else{
                eval('$redata["'.implode( '"]["', explode('/',$subSdfKey) ).'"] = $subDump;');
            }
        }
    }

    function getProductLvPrice($goodsId){
        $sql = 'SELECT goods_id, bn, spec_info, product_id, cost, price , mktprice FROM sdb_b2c_products WHERE goods_id IN ('.implode(',',$goodsId).')';

        $proList = $this->db->select($sql);

        $levelList = $this->db->select('SELECT goods_id, product_id, level_id, price AS mprice FROM sdb_b2c_goods_lv_price WHERE goods_id IN ('.implode(',',$goodsId).')');
        $returnData = array();
        $lvPrice = array();
        foreach( $levelList as $level )
            $lvPrice[$level['product_id']][$level['level_id']] = $level['mprice'] ;

        foreach( $proList as $pro )
            $returnData[$pro['goods_id']][] = array('product_id'=>$pro['product_id'],'bn'=>$pro['bn'], 'pdt_desc'=>$pro['spec_info'], 'price'=>$pro['price'], 'lv_price'=>$lvPrice[$pro['product_id']], 'cost'=>$pro['cost'],'mktprice'=>$pro['mktprice'] );


        return $returnData;
    }

    function getProductStore($goodsId){
        $sql = 'SELECT goods_id, bn, spec_info, product_id, store FROM sdb_b2c_products WHERE goods_id IN ('.implode(',',$goodsId).')';
        $proList = $this->db->select($sql);
        $returnData = array();
        foreach( $proList as $pro )
            $returnData[$pro['goods_id']][] = array( 'product_id'=>$pro['product_id'],'bn'=>$pro['bn'], 'pdt_desc'=>$pro['spec_info'], 'store'=>$pro['store'] );
        return $returnData;
    }

    function batchUpdateText( $goods_id, $updateType , $updateName , $updateValue ){ //review: 注意$this->db->quote 必要的数据
     $sql = 'UPDATE sdb_b2c_goods SET ';
        switch($updateType){
            case 'name':
                $sql .= $updateName.' = "'.$updateValue.'" WHERE goods_id in ('.implode(',',$goods_id).')';
                break;

            case 'add':
                $sql .= $updateName.' = CONCAT("'.$updateValue['front'].'",'.$updateName.',"'.$updateValue['after'].'") WHERE goods_id in ('.implode(',',$goods_id).')';
                break;

            case 'replace':
                $sql .= $updateName.' = REPLACE( '.$updateName.', "'.$updateValue['front'].'" , "'.$updateValue['after'].'" ) WHERE goods_id in ('.implode(',',$goods_id).') AND REPLACE( '.$updateName.', "'.$updateValue['front'].'" , "'.$updateValue['after'].'" ) != "" ';
                break;
        }
        $this->db->exec($sql);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,商品名称和商品简介@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
           if(method_exists($obj_operatorlogs,'batchUpdateText')){
             $obj_operatorlogs->batchUpdateText( $goods_id, $updateType , $updateName , $updateValue);
          }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,商品名称和商品简介@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function syncProNameByGoodsId($gids){
        $sql = 'UPDATE sdb_b2c_products p , sdb_b2c_goods g SET p.name= g.name WHERE g.goods_id = p.goods_id AND g.goods_id IN ('.(implode(',',$gids)).')';
        return $this->db->exec($sql);
    }

    function batchUpdateInt( $goods_id, $updateName, $updateValue , $tableName = '' ){
        $sql = 'UPDATE '.( $tableName?$tableName:'sdb_b2c_goods').' SET '.$updateName.' = '.$updateValue.' WHERE goods_id in ( '.implode(',', $goods_id).' )';
        $this->db->exec($sql);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,商品排序和分类转换@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
           if(method_exists($obj_operatorlogs,'batchUpdateInt')){
             $obj_operatorlogs->batchUpdateInt( $goods_id, $updateName, $updateValue , $tableName);
          }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,商品排序和分类转换@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateArray( $goods_id , $tableName, $updateName, $updateValue ){
        $addSql = array();
        foreach( $updateName as $k => $v )
            $addSql[] = $v.' = "'.$updateValue[$k].'" ';
        $sql = 'UPDATE '.$tableName.' SET '.implode(',', $addSql).' WHERE goods_id in ('.implode(',',$goods_id).') ';
        $this->db->exec($sql);
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,商品品牌@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
           if(method_exists($obj_operatorlogs,'batchUpdateArray')){
             $obj_operatorlogs->batchUpdateArray( $goods_id , $tableName, $updateName, $updateValue );
          }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,商品品牌@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateByOperator( $goods_id, $tableName, $updateName , $updateValue, $operator=null , $fromName = null ){
        $sql = '';  //review: 注意$this->db->quote 必要的数据
        if(is_numeric($fromName)){
            //根据会员价格调价
            $mem_lv_model = $this->app->model('member_lv');
            $dis_count = $mem_lv_model->get_dis_count($fromName);
            $fromName = $updateName.' * '.$dis_count;
        }

        $updateValue = trim($updateValue);
        if( $operator == '-' ){

            $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = 0 WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') AND '.$updateName.' IS NOT NULL AND '.( $fromName?$fromName:$updateName ).'<='.floatval($updateValue);
            $this->db->exec($sql);

            $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = '.( $fromName?$fromName:$updateName ).' '.$operator.' '.floatval($updateValue).' WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') AND '.$updateName.' IS NOT NULL AND '.( $fromName?$fromName:$updateName ).'>'.floatval($updateValue);
            $this->db->exec($sql);


        }else{
            if(empty($updateValue) && $updateValue !== '0'){
                $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = NULL  WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') ';
            }else{
                $sql = 'UPDATE '.$tableName.' SET '.$updateName.' = round('.( $operator?( $fromName?$fromName:$updateName ).' '.$operator.' '.$updateValue:'"'.$updateValue.'"' ).', 3) WHERE '.( strstr($tableName, ',')?' a.goods_id = b.goods_id AND a.':'' ).' goods_id IN ('.implode(',',$goods_id).') ';
            }

            $this->db->exec($sql);
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志，统一调价，调库存，调质量@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
           if(method_exists($obj_operatorlogs,'batchUpdateByOperator')){
             $obj_operatorlogs->batchUpdateByOperator( $goods_id, $tableName, $updateName , $updateValue, $operator, $fromName);
          }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志，统一调价，调库存，调质量@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateStore($store){
        foreach( $store as $goods ){
            foreach( $goods as $proId => $pstore ){
            	$pstore = trim($pstore);
                if($pstore === '0'){
                    $this->db->exec('UPDATE sdb_b2c_products SET store = 0 WHERE product_id = '.intval($proId));
                }elseif(empty($pstore)){
                    $this->db->exec('UPDATE sdb_b2c_products SET store = NULL WHERE product_id = '.intval($proId));
            	}else{
                    $this->db->exec('UPDATE sdb_b2c_products SET store = '.(intval($pstore)<0?0:intval($pstore)).' WHERE product_id = '.intval($proId));
            	}

            }
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志,分别调整商品库存@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
           if(method_exists($obj_operatorlogs,'batchUpdateStore')){
             $obj_operatorlogs->batchUpdateStore($store);
          }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志,分别调整商品库存@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function synchronizationStore($goods_id){
        $storeSum1 = $this->db->select('SELECT goods_id FROM sdb_b2c_products WHERE goods_id in ('.implode(',',$goods_id).') AND store IS NULL GROUP BY goods_id');
        $nullStore = array();
        foreach( $storeSum1 as $aStore ){
            $nullStore[$aStore['goods_id']] = 1;
        }
        $storeSum = $this->db->select('SELECT goods_id, sum(store) as storesum FROM sdb_b2c_products WHERE goods_id in ('.implode(',',$goods_id).') GROUP BY goods_id');
        foreach($storeSum as $v){
            $this->db->exec('UPDATE sdb_b2c_goods SET store = '.( isset( $nullStore[$v['goods_id']] )?'null':intval($v['storesum']) ).' WHERE goods_id = '.intval($v['goods_id']));
        }
        return true;
    }

    function batchUpdatePrice($pricedata){
        foreach( $pricedata as $updateName => $data ){
            if( in_array( $updateName , array( 'price', 'cost','mktprice' ) ) ) {
                foreach( $data as $goodsId => $goodsItem ){
                    foreach( $goodsItem as $proId => $price ){
                        $this->db->exec( 'UPDATE sdb_b2c_products SET '.$updateName.' = '.floatval(trim($price)).' WHERE product_id = '.intval($proId) );
                    }
                    $minPrice = $this->db->selectrow('SELECT MIN(price) AS mprice FROM sdb_b2c_products WHERE goods_id = '.intval($goodsId) );
                    if($updateName=='price')
                    $this->db->exec( 'UPDATE sdb_b2c_goods SET '.$updateName.' = '.floatval(trim($minPrice['mprice'])).' WHERE goods_id = '.intval($goodsId) );
                    else
                    $this->db->exec( 'UPDATE sdb_b2c_goods SET '.$updateName.' = '.floatval(trim($price)).' WHERE goods_id = '.intval($goodsId) );
                }
            }else{
                foreach( $data as $goodsId => $goodsItem )
                    foreach( $goodsItem as $proId => $price ){
                        if( $price == null || $price == '' ){
                            $this->db->exec('DELETE FROM sdb_b2c_goods_lv_price WHERE product_id = '.intval($proId).' AND level_id = '.intval($updateName).' AND goods_id = '.intval($goodsId));
                            continue;
                        }
                        $datarow = $this->db->selectrow('SELECT count(*) as c FROM sdb_b2c_goods_lv_price WHERE product_id = '.intval($proId).' AND level_id = '.intval($updateName).' AND goods_id = '.intval($goodsId));
                        if($datarow['c'] > 0)
                            $this->db->exec('UPDATE sdb_b2c_goods_lv_price SET price = '.floatval(trim($price)).' WHERE product_id = '.intval($proId).' AND level_id = '.intval($updateName).' AND goods_id = '.intval($goodsId));
                        else
                            $this->db->exec('INSERT INTO sdb_b2c_goods_lv_price (product_id, level_id, goods_id, price ) VALUES ( '.intval($proId).', '.intval($updateName).', '.intval($goodsId).', '.floatval($price).' )');
                    }
            }
        }
        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志，分别调价@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
           if(method_exists($obj_operatorlogs,'batchUpdatePrice')){
             $obj_operatorlogs->batchUpdatePrice($pricedata);
          }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志，分别调价@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        return true;
    }

    function batchUpdateMemberPriceByOperator( $goods_id, $updateLvId, $updateValue, $operator=null , $fromName = null ){
        $aallProductId = $this->db->select('SELECT product_id,goods_id FROM sdb_b2c_products WHERE goods_id IN ('.implode(',',$goods_id).')');
        $aupdateProductId = $this->db->select('SELECT product_id,goods_id FROM sdb_b2c_goods_lv_price WHERE goods_id IN ('.implode(',',$goods_id).') AND level_id = '.$updateLvId);
        $allProductId = array();
        $updateProductId = array();
        foreach( $aallProductId as $allv )
            $allProductId[$allv['product_id']] = $allv['goods_id'];
        foreach( $aupdateProductId as $alluv )
            $updateProductId[$alluv['product_id']] = $alluv['goods_id'];
        unset($aallProductId, $aupdateProductId);
        $insertProductId = array_diff_assoc( $allProductId, $updateProductId);

        if( $operator ){
            if( $updateValue ) {
                if( $fromName && is_numeric($fromName) ){        //用会员价修改会员价
                    $member_lv_Row = $this->db->selectrow("SELECT dis_count FROM sdb_b2c_member_lv WHERE member_lv_id = ".$fromName);
                    foreach( $updateProductId as $upProId => $upGoodsId ){
                        $dataRow = $this->db->selectrow('SELECT price FROM sdb_b2c_goods_lv_price WHERE level_id = '.$fromName.' AND product_id = '.$upProId.' AND goods_id = '.$upGoodsId);
                        if(!$dataRow){
                            $dataprice_Row = $this->db->selectrow("SELECT price AS price FROM sdb_b2c_products WHERE product_id = ".$upProId);
                            $dataRow['price'] = $dataprice_Row['price'] * floatval($member_lv_Row['dis_count']);
                        }
                        $isup_flag1 = floatval($dataRow['price']).$operator.floatval($updateValue);//修复会员价为负情况，判断修改后的价格是否小于0，是则不做修改@lujy
                        eval("\$isup_flag1=$isup_flag1;");
                        if( $isup_flag1>0){
                            $this->db->exec('UPDATE sdb_b2c_goods_lv_price SET price = '.$dataRow['price'].$operator.floatval($updateValue).' WHERE goods_id = '.$upGoodsId.' AND level_id = '.$updateLvId.' AND product_id = '.$upProId.'');
                        }
                    }
                    foreach( $insertProductId as $inProId => $inGoodsId ){
                        $dataRow = $this->db->selectrow('SELECT price FROM sdb_b2c_goods_lv_price WHERE level_id = '.$fromName.' AND product_id = '.$inProId.' AND goods_id = '.$inGoodsId);
                        if(!$dataRow)
                        {
                         $dataprice_Row = $this->db->selectrow("SELECT price AS price FROM sdb_b2c_products WHERE product_id = ".$inProId);
                         $dataRow['price'] = $dataprice_Row['price'] * floatval($member_lv_Row['dis_count']);
                        }
                        $isup_flag2 = floatval($dataRow['price']).$operator.floatval($updateValue);
                        eval("\$isup_flag1=$isup_flag2;");
                        if( $isup_flag2>0){
                            $this->db->exec('INSERT INTO sdb_b2c_goods_lv_price ( product_id, level_id, goods_id, price ) VALUES ('.$inProId.', '.$updateLvId.', '.$inGoodsId.', '.$dataRow['price'].$operator.floatval($updateValue).')');
                        }
                    }
                }else{          //用市场价、销售价、成本价修改会员价
                    foreach( $updateProductId as $upProId => $upGoodsId ){
                        $dataRow = array();
                        $upGoodsId = intval($upGoodsId);
                        if( $fromName == 'price' )
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_products WHERE product_id = '.$upProId);
                        else
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_goods WHERE goods_id = '.$upGoodsId);
                        $isup_flag3 = floatval($dataRow['price']).$operator.floatval($updateValue);
                        eval("\$isup_flag3=$isup_flag3;");
                        if( $isup_flag3>0){
                            $this->db->exec('UPDATE sdb_b2c_goods_lv_price SET price = '.$dataRow['price'].$operator.floatval($updateValue).' WHERE product_id = '.$upProId.' AND goods_id = '.$upGoodsId.' AND level_id = '.$updateLvId);
                        }
                    }
                    foreach( $insertProductId as $inProId => $inGoodsId ){
                        $inGoodsId = intval($inGoodsId);
                        $dataRow = array();
                        if( $fromName == 'price' )
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_products WHERE product_id = '.$inProId);
                        else
                            $dataRow = $this->db->selectrow('SELECT '.$fromName.' AS price FROM sdb_b2c_goods WHERE goods_id = '.$inGoodsId);
                        $isup_flag4 = floatval($dataRow['price']).$operator.floatval($updateValue);
                        eval("\$isup_flag4=$isup_flag4;");
                        if( $isup_flag4>0){
                            $this->db->exec('INSERT INTO sdb_b2c_goods_lv_price ( product_id, level_id, goods_id, price ) VALUES ('.$inProId.', '.$updateLvId.', '.$inGoodsId.', '.$dataRow['price'].$operator.floatval($updateValue).')');
                        }
                    }
                }
            }

        }else{
             if( $updateValue != null && $updateValue !='' ){
                foreach( $updateProductId as $upProId => $upGoodsId ){
                    $upGoodsId = intval($upGoodsId);
                    $this->db->exec( 'UPDATE sdb_b2c_goods_lv_price SET price = '.floatval($updateValue).' WHERE goods_id = '.intval($upGoodsId).' AND level_id = '.intval($updateLvId).' AND product_id = '.intval($upProId));
                }
                foreach( $insertProductId as $inProId => $inGoodsId ){
                    $this->db->exec( 'INSERT INTO sdb_b2c_goods_lv_price ( product_id, level_id, goods_id, price ) VALUES ('.intval($inProId).', '.intval($updateLvId).', '.intval($inGoodsId).', '.floatval($updateValue).')') ;
                }
             }else{
                $this->db->exec('DELETE FROM sdb_b2c_goods_lv_price WHERE goods_id IN ( '.implode(',',$goods_id).' ) AND level_id = '.intval($updateLvId));
             }
        }

        #↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓记录管理员操作日志，统一调会员价@lujy↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
        if($obj_operatorlogs = kernel::service('operatorlog.goods')){
             if(method_exists($obj_operatorlogs,'batchUpdateMemberPriceByOperator')){
                 $obj_operatorlogs->batchUpdateMemberPriceByOperator( $goods_id, $updateLvId, $updateValue, $operator, $fromName);
             }
        }
        #↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑记录管理员操作日志，统一调会员价@lujy↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

        return true;
    }

    function getGoodsSellLogList($gid,$page,$limit=20){
        $sql = 'SELECT * FROM sdb_b2c_sell_logs WHERE goods_id = '.intval($gid).' ORDER BY log_id DESC ';
        return $this->db->selectPager($sql,$page,$limit);
    }

    function getBatchEditInfo($filter){
        $r = $this->db->selectrow('select count( goods_id     q1) as count from sdb_b2c_goods where '.$this->_filter($filter));
        return $r;
    }

    function getGoodsSellLogNum($gid){
        $res = $this->db->selectrow('SELECT count(log_id) as totalnum FROM sdb_b2c_sell_logs WHERE goods_id = '.intval($gid));
        return intval($res['totalnum']);
    }

    /**
     * 如果商品下架则货品也要下架
     *
     * @param $goods_id 商品id
     * @param $status 商品的上下架状态
     * author @lujy
     */
    function pro_unmarketable($goods_id,$status){
        $result = array('goods_id'=>$goods_id);
        $data = array('marketable'=>$status);
        $is_update = $this->update($data,$result);

		//$this->storekv_umarkable_product($goods_id,$status);
		return $is_update;
    }



	///**
	// * 商品改造信息从kvstore里面存储的方法 - 货品需要下架判断
	// * @param int goods id
	// * @param string status
	// */
	//public function storekv_umarkable_product($goods_id,$status){
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_info_'.$goods_id,$goods);

	//	/** 取到商品对应的所有规格 **/
	//	$arr_product = $this->getList('product_id',array('goods_id'=>$goods_id));
	//	foreach ((array)$arr_product as $product){
	//		$goods['product'][$product['product_id']]['status'] = $status;
	//	}

	//	base_kvstore::instance('_ec_optimize')->store('goods_info_'.$goods_id,$goods);
	//}

	/**
	 * 货品信息修改 - 保存到kvstore里面
	 * @param int goods id
	 * @param array 商品数据
	 */
	//public function storekv_product_info($goods_id,$data=array()){
	//	$product = array();

	//	$product[$data['product_id']] = array(
	//		'product_id'=>$data['product_id'],
	//		'bn'=>$data['bn'],
	//		'goods_id'=>$goods_id,
	//		'spec_desc'=>$data['spec_desc'],
	//		'status'=>$data['status'],
	//	);

	//	base_kvstore::instance('_ec_optimize')->fetch('goods_info_'.$goods_id,$goods);
	//	if (!$goods) return;
	//	if (!$goods['product']||!$goods['product'][$data['product_id']]) return;

	//	$goods['product'][$data['product_id']] = $product;
	//	/** 存储商品 **/
	//	base_kvstore::instance('_ec_optimize')->store('goods_info_'.$goods_id,$goods);

	//	/** 更新价格 **/
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_price_'.$goods_id,$price);
	//	$price['product'][$data['product_id']]['price'] = $data['price'];
	//	base_kvstore::instance('_ec_optimize')->store('goods_price_'.$goods_id,$price);

	//	/** 库存保存 **/
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_store_'.$goods_id,$store);
	//	$store['product'][$data['product_id']] = $data['store'];
	//	base_kvstore::instance('_ec_optimize')->store('goods_store_'.$goods_id,$store);
	//}

	/**
	 * 获取指定货品信息
	 * @param int goods id
	 * @param int product id
	 * @return array 货品数据
	 */
    //public function getkv_product_info($goods_id,$product_id){
	//	base_kvstore::instance('_ec_optimize')->fetch('goods_info_'.$goods_id,$goods);
	//	if (!$goods) return;
	//	if (!$goods['product']||!$goods['product'][$product_id]) return;
	//
	//	return $goods['product'][$product_id];
	//}
}
