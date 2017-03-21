<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

class search_search_lucence implements search_interface_search{
    var $name = '二元分词搜索';
    var $description = '基于zend_lucence开发的文本搜索引擎';
    function __construct(){
        $this->dir = ROOT_DIR.'/data/search/zend/lucene/';
        if(!is_dir($this->dir))  utils::mkdir_p($this->dir, 0777);
    }

    function link(){
        $this->obj = Zend_Search_Lucene::open($this->dir,true);
        return $this->obj;
    }

    function select($queryArr=array()){
      $this->query($queryArr);
      return $this->commit();
    }
    function query($queryArr=array()){
    	 $this->create();
    	 $this->from = $queryArr['from'];
         $this->to = $queryArr['to'];
         $this->order = $this->setorderby($queryArr['order']);

         unset($queryArr['from']);
         unset($queryArr['to']);
         unset($queryArr['orderby']);
         $queryArr = $this->prepaData($queryArr);
         $index = new Zend_Search_Lucene($this->dir);
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);
         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);

         if(isset($queryArr['title'])){
         	 return false;
        }

         if(is_array($queryArr['cat_id'])){
              $cat_id = '('.implode(' OR ',$queryArr['cat_id']).')';
              unset($queryArr['cat_id']);
         }

         if(is_array($queryArr)){
             foreach($queryArr as $k=>$v){
                    $query[] = $k.':'.$v;
             }
             if(!empty($cat_id))
                 $query[] .= $cat_id;
         }

         if(is_array($query))
             $this->query = implode(' AND ',$query);



    }

    function commit(){
        if(isset($this->query)){//print_R($this->order);exit;
            if(is_array($this->order))
                $result = $this->obj->find($this->query,$this->order['order_name'],$this->order['order_type'],$this->order['order_by']);
            else
                $result = $this->obj->find($this->query);
        }

        $rfilter['goods_id'] =array();
        if(!empty($result)){
            foreach($result AS $obj){
                    $document = $obj->getDocument($obj->id);
                    $goodsId = $document->getFieldValue('goods_id');
                    if(!in_array($goodsId,$rfilter['goods_id'])&&!empty($goodsId))
                        array_push($rfilter['goods_id'],$document->getFieldValue('goods_id'));

            }
        }
        $rfilter['total'] = count($rfilter['goods_id']);

        if(isset($this->from)&&isset($this->to)){
            $rfilter['result'] = array_slice($rfilter['goods_id'],$this->from,$this->to);
        }

        return $rfilter;
    }

    function insert($val=array()){
         $this->link();
         $index = new Zend_Search_Lucene($this->dir);
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);

         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);
         $doc = new Zend_Search_Lucene_Document();

         $doc->addField(Zend_Search_Lucene_Field::Text('goods_id',$val['goods_id']));
         if(isset($val['product'][0]['price']['price']['price'])){
             $pric = $val['product'][0]['price']['price']['price'];
         }else{
             if(is_array($val['product'])){
                foreach($val['product'] as $kp=>$vp){
                     $pric = $vp['price']['price']['price'];
                }
             }
         }
         $doc->addField(Zend_Search_Lucene_Field::UnStored('cat_id', $val['category']['cat_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('brand_id',$val['brand']['brand_id']));
         $doc->addField(Zend_Search_Lucene_Field::UnIndexed('last_modify',time()));
         $doc->addField(Zend_Search_Lucene_Field::UnIndexed('price',$this->priceChange($pric)));
         $doc->addField(Zend_Search_Lucene_Field::UnStored('marketable','true'));
         if(isset($val['props'])){
             for($i=1;$i<=28;$i++){
                $p = 'p_'.$i;
                $doc->addField(Zend_Search_Lucene_Field::UnStored($p,$val['props'][$p]['value']));
             }
         }
         if(is_array($val['keywords'])){
             foreach($val['keywords'] as $k=>$v){
                 $keyword.= '#'.$v['keyword'].'@';
             }
         }

         if(is_array($val['product'])){
             foreach($val['product'] as $k=>$v){
                 if(is_array($v['spec_desc']['spec_value_id'])){
                     foreach($v['spec_desc']['spec_value_id'] as $key=>$vals){
                            $spec.= '#'.$key.$vals.'@';
                     }
                 }
                 $bn.= '#'.$v['bn'].'@';
             }
         }

        $name = '#'.$val['name'].'@';

        $doc->addField(Zend_Search_Lucene_Field::UnStored('title',$name,'utf8'));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('keyword',$keyword));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('spec',$spec));
        $doc->addField(Zend_Search_Lucene_Field::UnStored('bn','#'.$val['bn'].'@'.$bn));
        $index->addDocument($doc);
        return $index->commit();



    }
    /*
     * 字母形式才能比较价格大小
     * */
    function priceChange($price){
        $price = strval(intval($price));
        if(strlen($price)<8){
            for ($i=1;$i<=8-strlen($price);$i++){
                $tmpstr .= "0";
            }
        }
        $result = $tmpstr.$price;
        $word = array('a','b','c','d','e','f','g','h','i','j');
        $num = array('0','1','2','3','4','5','6','7','8','9');
        $result = str_replace($num,$word,$result);
        return $result;
    }



    function update($val=array(),$where){
         $this->link();
         $data= array();
         if(isset($val['goods_id'])){
             $this->insert($val);
             $res = $this->commit();
             if(isset($res)){
                 foreach($res AS $obj){
                     if(isset($obj->id))
                        $this->obj->delete($obj->id);
                 }
             }
         }
         return $this->insert($val);
   }

    function delete($val=array()){
         $this->link();
         $data= array();
         if(isset($val['goods_id'])){
             $data['goods_id'] = $val['goods_id'];
             $this->query($data);
             $res = $this->commit();
         }
         if(isset($res)){
             foreach($res AS $obj){
                    return $this->obj->delete($obj->id);
             }
         }

    }

    function prepaData($filter){
        if(isset($filter['from']))
            $data['from'] =  $filter['from'];
        if(isset($filter['to']))
            $data['to'] =  $filter['to'];
        if(isset($filter['orderby']))
            $data['orderby'] =  $filter['orderby'];
        if(isset($filter['last_modify']))
            $data['last_modify'] =  $filter['last_modify'];

        if(!empty($filter['goods_id']))
            $data['goods_id'] =  $filter['goods_id'][0];
        if(!empty($filter['name'])){
            $data['title'] =  '#'.$filter['name'][0].'@';
        }
        if(!empty($filter['cat_id'][0])){
        	$cat_id = $filter['cat_id'][0];
        	$filter['cat_id'][0] = 'cat_id:'.$filter['cat_id'][0];
      	    $objCat = app::get('b2c')->model('goods_cat');
        	$list = $objCat->get_cat_list();
        	foreach($list as $val){
                if($val['pid'] == $cat_id)
                    $filter['cat_id'][] = 'cat_id:'.$val['cat_id'];
        	}
        	$data['cat_id'] =  $filter['cat_id'];
        }

        if(!empty($filter['bn'][0]))
            $data['bn'] =  '#'.$filter['bn'][0].'@';
        if(!empty($filter['brand_id'][0]))
            $data['brand_id'] =  $filter['brand_id'][0];
        for($i=1;$i<=28;$i++){
            $p = 'p_'.$i;
            if(isset($filter[$p][0])){
                $data[$p] =  $filter[$p][0];
            }
        }
        for($i=1;$i<=10;$i++){
            $s = 's_'.$i;
            if(!empty($filter[$s][0])){
                $data['spec'] =  '#'.$i.$filter[$s][0].'@';
            }
        }

        if(!empty($filter['price'][0])&&!empty($filter['price'][1])){
            $minPrice =  $this->priceChange($filter['price'][0]);
            $maxPrice =  $this->priceChange($filter['price'][1]);
            $data['price'] = '{'.$minPrice.' TO '.$maxPrice.'}';
        }

        return $data;


    }

    function reindex(&$msg){       // 重建索引
         $index = $this->create();
         $analyzerObj = new search_instance_analyzer_cjk;
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_goods);
         $analyzerObj->addPreFilter(new search_instance_analyzer_filter_cjk);
         $analyzerObj->addFilter(new search_instance_token_filter_lowercase);
         Zend_Search_Lucene_Analysis_Analyzer::setDefault($analyzerObj);
         $doc = new Zend_Search_Lucene_Document();
         $data = app::get('b2c')->model('goods')->getlist('*',array());

         foreach($data as $key=>$val){
	         $doc->addField(Zend_Search_Lucene_Field::Text('goods_id',$val['goods_id']));
	         $doc->addField(Zend_Search_Lucene_Field::UnStored('cat_id', $val['cat_id']));
	         $doc->addField(Zend_Search_Lucene_Field::UnStored('brand_id',$val['brand_id']));
	         $doc->addField(Zend_Search_Lucene_Field::UnStored('price',$this->priceChange($val['price'])));
	         $doc->addField(Zend_Search_Lucene_Field::UnStored('marketable',$val['marketable']));
             $doc->addField(Zend_Search_Lucene_Field::UnIndexed('last_modify',$val['last_modify']));
	         for($i=1;$i<=28;$i++){
	            $p = 'p_'.$i;
	            $doc->addField(Zend_Search_Lucene_Field::UnStored($p,$val[$p]));
	         }


/*	         foreach($val['keywords'] as $k=>$v){
	             $keyword.= '#'.$v['keyword'].'@';

	         }*/

	         if(is_array($val['spec_desc'])){
	              foreach($val['spec_desc'] as $k=>$v){
	                  foreach($v as $key=>$vals){
                          $spec.= '#'.$k.$vals['spec_value_id'].'@';
	                  }
	              }
	         }

	        $name = '#'.$val['name'].'@';

	        $doc->addField(Zend_Search_Lucene_Field::UnStored('title', $name));
	        //$doc->addField(Zend_Search_Lucene_Field::UnStored('keyword',$keyword));
	        $doc->addField(Zend_Search_Lucene_Field::UnStored('spec',$spec));
	        $doc->addField(Zend_Search_Lucene_Field::UnStored('bn','#'.$val['bn'].'@'));
	        unset($p);
	        unset($spec);
	        $index->addDocument($doc);
        }
        $msg = '重建索引成功';
        return true;
    }

    function setorderby($order){
    	switch($order){
    		case 'last_modify desc':
    		     return array(
    		         'order_name'=>'last_modify',
    		         'order_type'=>SORT_NUMERIC,
    		         'order_by'=>SORT_DESC,
    		     );
    		break;
    	    case 'last_modify':
    		     return array(
    		         'order_name'=>'last_modify',
    		         'order_type'=>SORT_NUMERIC,
    		         'order_by'=>SORT_ASC,
    		     );
    		break;
    		case 'price desc':
    		     return array(
    		         'order_name'=>'price',
    		         'order_type'=>SORT_STRING,
    		         'order_by'=>SORT_DESC,
    		     );
    		break;
    	    case 'price':
    		     return array(
    		         'order_name'=>'price',
    		         'order_type'=>SORT_STRING,
    		         'order_by'=>SORT_ASC,
    		     );
    		break;
    		default:
                  return array(
    		         'order_name'=>'goods_id',
    		         'order_type'=>SORT_NUMERIC,
    		         'order_by'=>SORT_DESC,
    		     );
            break;
    	}
    }

    function optimize(&$msg){
        if(file_exists($this->dir.'/segments.gen')){
            $this->link()->optimize();
             $msg = '优化成功';
             return true;
        }else{
            $msg = '当前服务器没有索引文件';
            return false;
        }
    }

    function finder_capability($content){
    	 $render = app::get('search')->render();
    	 $render->pagedata['type'] = $content['content_name'];
         $render->pagedata['name'] = $content['content_path'];
         return $render->fetch('capability/goods.html');
    }

    function status(&$msg){
        if(file_exists($this->dir.'/segments.gen')){
            if(is_object($this->link())){
                 $msg = '已建立连接';
                 return true;
            }else{
                 $msg = '连接状态异常';
                 return false;
            }
        }else{
            $msg = '当前服务器没有索引文件';
            return false;
        }
    }

    function clear(&$msg){
        $msg = '无清空方法';
        return false;
    }
}
