<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/**
 * mdl_search
 *
 * @uses modelFactory
 * @package
 * @version $Id: mdl.search.php 1867 2008-06-05 04:00:24Z flaboy $
 * @copyright 2003-2008 ShopEx
 * @author Liujy <ever@shopex.cn>
 * @license Commercial
 */

class b2c_mdl_search {

    var $map = array(
            'brand_id'=>'b',
            'price'=>'p',
            'tag'=>'t',
            'search_keywords'=>'n',
            'bn'=>'f',
            'type_id'=>"tp"
        );

    function join($j){
        $v = array();
        foreach((array)$j as $n){
            $n = trim($n);
            if($n!=='')$v[] = rawurlencode($n);
        }
        return count($v)>0?implode(',',$v):false;
    }

    function encode($filter){
        $ret = array();
        $tmpSpec = array();
        if( $filter ){
            foreach($filter as $k=>$j){
                if($p = $this->map[$k]){
                    if(false!==($v = $this->join($j)))
                        $ret[$p] = $p.','.$v;

                }elseif(substr($k,0,2)=='p_'){
                    if(false!==($v = $this->join($j)))
                        $ret[$n = substr($k,2)] = $n.','.$v;
                }
                elseif(substr($k,0,2)=="s_"){
                   //$ret[$k]="s,".substr($k,2)."|".$this->join($j);
                   $ret[$k]="s".substr($k,2).",".$this->join($j);
                }
            }
        }
        return implode('_',$ret);
    }
    function decode($str,&$path,&$system){
        $data = array();
        if($str){
            foreach(explode('_',$str) as $substr){
                $data[] = $substr;
                $columns = explode(',',$substr);
                $part1 = array_shift($columns);
                $map = array_flip($this->map);
                if(is_numeric($part1)){
                    $filter['p_'.$part1] = $columns;
                    $title = '';
                    $p = $part1;
                }elseif (substr($part1,0,1)=="s"){
                    /*$tmp=explode("|",$columns[0]);
                    $filter['s_'.$tmp[0]]=array($tmp[1]);
                    $p='s_'.$tmp[0];*/
                    $filter['s_'.substr($part1,1)] = $columns;
                    $p="s_".substr($part1,1);
                    $columns[0]=substr($part1,1).",".$columns[0];
                }elseif($p = $map[$part1]){
                    $filter[$p] = $columns;
                }else{
                    $title='';
                }
                $path[] = array('type'=>$p,'data'=>$columns,'str'=>implode('_',$data));
            }
            return $filter;
        }
    }
}
