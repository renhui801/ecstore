<?php

class search_search_sphinx implements search_interface_search{

    var $name = 'sphinx搜索';
    var $description = '基于sphinxql开发的搜索引擎';
    var $_index = null;

    /*
     *__construct 初始化类,连接sphinx服务
     * */
    public function __construct($index=null) {
        if($index) $this->_index = $index;
        $this->sphinx_config = $this->_config();
        if (is_array ( $this->sphinx_config )) {
            $this->link = $this->link();
            return $this;
        }
    }//End Function

    /*
     * link 连接sphinxql
     *
     * @access protected
     * @return obj
     * */
    public function link() {
        if (is_resource ( $this->link )){
            return $this->link;
        }
        $connect = ($this->sphinx_config['pconnect'] == true) ? 'mysql_pconnect' : 'mysql_connect';
        $link = $connect($this->sphinx_config['host']);
        if (!$link) {
            trigger_error(app::get('search')->_('无法连接sphinx服务').mysql_error().E_USER_ERROR);
        }
        return $link;
    }//End Function

    /*
     * _config 获取sphinxql连接配置信息
     *
     * @access private
     * @return array
     * */
    private function _config()
    {
        if(defined('SPHINX_SERVER_HOST') && constant('SPHINX_SERVER_HOST')){
            $option ['host']= SPHINX_SERVER_HOST;
        }
        $sphinx_config['host'] = ($option ['host'] ? $option ['host'] : '127.0.0.1:9306');
        if(defined('SPHINX_PCONNECT') && constant('SPHINX_PCONNECT')){
            $sphinx_config['pconnect']  == true;
        }
        return $sphinx_config;
    }//End Function

     /*
     * get_rt 获取索引的索引类型(实时|非实时)
     *
     * @access public
     * @return array 返回索引类型（实时|非实时）
     * */
    public function get_rt(){
        $list = $this->query('show tables');
        foreach($list as $val){
            if($val['Index'] == $this->_index && $val['Type'] == 'rt'){
                return true;
            }
        }
        return false;
    }//End function

    /*
     *exec 直接执行sphinxql语句
     *
     * @params string $sql sphinxql语句
     * @access public
     * @return source $rs
     * */
    public function exec($sql){
        $rs = mysql_query($sql,$this->link);
        if(!$rs && $this->link){
            $this->_error($sql);
        }
        return $rs;
    }//End Function

    /*
     *_error 记录错误和警告log
     *
     * @params string $query_sql 执行的sphinxql语句
     * @access public
     * */
    public function _error($query_sql){
        $br = "\r\n\t\t\t\t";
        $msg = 'sphinxql:'.$query_sql;

        if(mysql_error()){
            $error = mysql_error();
            $msg .= $br.'ERROR :'.$error;
        }
        $warnings = $this->query('SHOW WARNINGS');
        if($error || $warnings){
            if($warnings){
                foreach($warnings as $row){
                    $msg .= $br.'WARNING ('.$row['Code'].'):'.$row['Message'];
                }
            }
            logger::error($msg);
        }
    }//End Function

    /*
     * query 执行sphinxql语句返回对应的数据
     * @params string $sql sphinxql语句
     * @access public
     * @return array $data 查找到的数据
     * */
    public function query($sql){
        $rs = $this->exec($sql);
        if($rs && !is_bool($rs)){
            $data = array();
            while($row = mysql_fetch_assoc($rs)){
                $data[]=$row;
            }
        }else{
            $data = $rs ? true : false;
        }
        return $data;
    }//End Function


    /*
     * select 在索引中搜索到索引ID
     *
     * @params array $queryArr 搜索条件
     * @access public
     * @return array 返回索引ID
     * */
    public function select($queryArr=array()){
        $sphinxql = $this->_sphinxql($queryArr);
        $data = $this->query($sphinxql);
        $total = $this->show_meta();
        $list['data'] = $data;
        $list['total_found'] = $total[1]['Value'];
        return $list;
    }//End Function

    /*
     * insert 插入一条索引(只能是实时索引有效)
     *
     * @params array $queryArr
     * @access public
     * @return bool
     * */
    public function insert($queryArr=array()){
        $rt = $this->get_rt();
        if(!$rt){
            trigger_error(app::get('search')->_('插入只支持实时索引').E_USER_ERROR);
        }
        $fieldsArr = array_keys($queryArr);
        //插入索引必须要id
        if(!in_array('id',$fieldsArr)){
            $fieldsArr[] = 'id';
            $listid_sphinxql = 'select id from '.$this->_index.' order by id desc limit 1';
            $list = $this->query($listid_sphinxql);
            $queryArr [] = intval($list[0]['id'])+1;
        }
        $fields = implode(',',$fieldsArr);
        $values = implode("','",$queryArr);
        if($fields && $values){
            $sphinxql = 'INSERT INTO '.$this->_index.' ( '.$fields.' ) VALUES ( \''.$values.'\' )';
        }
        $res = $this->query($sphinxql);
        return $res;
    }//End Function

    /*
     * update 更新索引
     *
     * @params array $queryArr 需要更新的数据
     * @where  array $where    更新的条件
     * @access public
     * @return bool
     * */
    public function update($queryArr=array(),$where=array()){
        if(is_array($queryArr)){
            foreach($queryArr as $uint=>$value){
                if(is_array($value)){
                    $setArr[] = $uint .' = ('.implode(',',$value).')';
                }else{
                    $setArr[] = $uint .' = '.$value;
                }
            }
            $set = implode(',',$setArr);
        }else{
            $set = $queryArr;
        }
        $where = $this->filter($where);
        if($where) $where_str = ' WHERE '.$where;
        $sphinxql = 'UPDATE '.$this->_index .' SET '.$set.$where_str;
        $res = $this->query($sphinxql);
        return $res;
    }//End Function

    /*
     *delete 删除索引(只支持实时索引)
     *
     **/
    public function delete($queryArr=array()){
        $rt = $this->get_rt();
        if(!$rt){
            trigger_error(app::get('search')->_('删除只支持实时索引').E_USER_ERROR);
        }
        $where = $this->filter($queryArr);
        $sphinxql = 'DELETE FROM '.$this->_index.' WHERE ' . $where;
        $res = $this->query($sphinxql);
        return $res;

    }//End Function

    /*
     *show_meta 获取上一条显示查询状态信息
     *@access public
     * */
    public function show_meta(){
        return $this->query('show meta');
    }//End Function

    /*
     *BuildExcerpts 高亮显示
     *
     * @params string $text  待高亮的字符串
     * @params string $words 搜索的字符串
     * @params array  $opts  sphinx BuildExcerpts的opt参数
     * @params string $index 索引名称
     * @access public
     * @return string        添加过标签的字符串
     * */
    public function BuildExcerpts($text,$words,$opts=array(),$index=null){
      if(!$index) $index = $this->_index;
      if(empty($opts)){
          $opts=array(
              'before_match'=>'<font color=\"red\">',
              'after_match'=>'</font>'
          );
      }
      foreach($opts as $key=>$val){
          $opts_str .= " '".$val."' as ". $key .",";
      }

      $sphinxql = "CALL SNIPPETS('".$text."' , ".$index." , '".$words."' , ".substr($opts_str,0,-1)." )";
      $res = $this->query($sphinxql);
      return $res[0]['snippet'];
    }//End Function


    /*
     * get_describe 获取对应索引中可搜索的字段
     * @params string $index 索引名称
     * @access public
     * @return array $columns 可以索引字段
     * */
    public function get_describe($index=null){
        if(!$index) $index = $this->_index;
        $columns = app::get('search')->getConf('describe_'.$index);
        if(!$columns){
            $columns = $this->set_describe($index);
        }
        return $columns;
    }

     /*
     * set_describe 设置对应索引中可搜索字段
     * @params string $index 索引名称
     * @access public
     * @return array $columns 可以索引字段
     * */
    public function set_describe($index=null){
        if(!$index) $index = $this->_index;
        $setConfIndex = $index;
        $res = $this->query('show tables');
        foreach($res as $index_row){
            if($index == $index_row['Index'] && $index_row['Type'] == 'distributed'){
                $index = $index.'_merge';
            }
        }
        if($index){
            $sql = 'DESCRIBE '.$index;
        }else{
            trigger_error(app::get('search')->_('索引名称为空').mysql_error().E_USER_ERROR);
        }
        $data = $this->query($sql);
        $columns = array();
        foreach($data as $key=>$val){
            if($val['Type'] != 'field'){//可返回字段
                $columns['int'][] = $val['Field'];
            }else{//可检索字段
                $columns['field'][] = $val['Field'];
            }
            $columns['all'][] = $val['Field'];
        }
        app::get('search')->setConf('describe_'.$setConfIndex,$columns);
        return $columns;
    }//End Function

    /*
     * reindex 重建索引(执行脚本) todo
     * */
    public function reindex(&$msg){
        $msg = '无需重建索引';
        return false;
    }//End Function

    /*
     *optimize 优化索引
     **/
    public function optimize(&$msg){
        $msg = '无需优化索引';
        return false;
    }//End Function

    /*
     * 获取sphinx的运行状态
     *
     * */
    public function status(&$msg){
        $status = $this->query('SHOW STATUS');
        if($status[1]['Variable_name'] == 'connections' || $status[1]['Counter'] == 'connections'){
            $msg = '已建立连接';
            return $status;
        }else{
            $msg = '连接状态异常';
            return false;
        }
    }//End Function

    public function clear(&$msg){
        $msg = '无清空方法';
        return false;
    }//End Function

    /*
     * _sphinxql 根据搜索条件生成sphinxql语句
     *
     * @params array  $queryArr  搜索条件
     * @access public
     * @return string $query     sphinxql语句
     * */
    public function _sphinxql($queryArr){
        $this->search_setting = app::get('search')->getConf('search_index_setting_'.$this->_index);
        $this->cols = $queryArr['cols'] ? $queryArr['cols'] : '*';
        $this->offset = ($queryArr['offset'] && $queryArr['offset']>=0) ? intval($queryArr['offset']) : 0;
        $this->limit = ($queryArr['limit'] && $queryArr['limit'] != -1) ? intval($queryArr['limit']) : 100;
        $this->groupBy = $queryArr['groupBy'] ? ' GROUP BY '.$queryArr['groupBy'] : '';
        $this->orderBy = $queryArr['orderBy'] ? ' ORDER BY '.$queryArr['orderBy'] : ' ORDER BY '.$this->search_setting['order_value'].' '.$this->search_setting['order_type'];
        $this->option = $queryArr['option'] ? $queryArr['option'] : array('ranker'=>$this->search_setting['ranker']);
        if(is_string($queryArr['search_keywords'])){
            $this->search =  sprintf("MATCH('%s')", addslashes($queryArr['search_keywords']));
            $where[] = $this->search;
        }
        if(is_string($queryArr['filter']['filter_sql'])){
            $where[] = $queryArr['filter']['filter_sql'];
        }

        if($queryArr['filter']){
            $where[] = $this->filter($queryArr['filter']);
        }
        if($where){
            $where_str = ' WHERE ' . implode(' AND ',$where);
        }
        if(!empty($this->option)){
            $option_str = ' OPTION ';
            foreach($this->option as $key=>$row){
                if($row){
                    $option_str .= $key.'='.$row;
                }
            }
        }
        $query = 'SELECT '. $this->cols . ' FROM ' . $this->_index . $where_str .$this->groupBy. $this->orderBy.'  limit ' . $this->offset.','.$this->limit.$option_str;
        return $query;
    }//End Function

    /*
     * filter sphinxql的条件组织(不包含match)
     *
     * @params array $filter
     * @access public
     * @return string
     * */
    public function filter($filter){
        if(!$filter) return '';
        if(!is_array($filter)) return addslashes($filter);
        $columns = $this->get_describe();
        foreach($filter as $key=>$val){
            $type_info = explode('|',$key);
            if(!in_array($type_info[0],$columns['int'])){
              unset($filter[$type_info[0]]);
              continue;
            }
            $_str = $this->_inner_getFilterType($type_info[1],$val);
            if( strpos($_str,'{field}')!==false ){
                $where[] = str_replace('{field}',$type_info[0],$_str);
            }else{
                $where[] = $type_info[0].$_str;
            }
        }
        return implode(' AND ',$where);
    }//End Function

    /*
     * _inner_getFilterType 转换运算符号
     *
     * @params  string      $type 要转换的类型
     * @params  int|array   $var  运算符号对应的值
     * @access  public
     * @return  string
     * */
    public function _inner_getFilterType($type,$var){
        if(!is_array($var) && !$type){
            $type = 'nequal';
        }
        if(is_array($var) && !$type){
          $type = 'in';
        }
        $FilterArray=
            array(
                'than'=>' > '.$var,
                'lthan'=>' < '.$var,
                'nequal'=>' = '.$var,
                'noequal'=>' <> '.$var,
                'tequal'=>' = '.$var,
                'sthan'=>' <= '.$var,
                'bthan'=>' >= '.$var,
                'between'=>' {field}>='.$var[0].' and '.' {field}<='.$var[1],
                'in' =>" in (".implode(",",(array)$var).") ",
                'notin' =>" not in (".implode(",",(array)$var).") ",
            );
        return $FilterArray[$type];
    }//End Function

}//End Class


