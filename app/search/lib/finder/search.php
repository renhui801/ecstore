<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class search_finder_search
{
    public $addon_cols='content_name,content_path';
    public $column_service_type = '服务项目';
    public $column_description = '描述';
    public $column_reindex = '索引';
    public $column_status = '状态';
    public $column_used = '使用';
    public $column_used_width = '80';
    public $column_reindex_width = '80';
    public $column_status_width = '80';
    public $column_description_width = '300';
    public $detail_capability = '基本信息';
    public $detail_config = '配置';

    public function detail_config($id){
        $content = app::get('base')->model('app_content')->dump(array('content_id'=>$id),'*');
        $obj = kernel::single($content['content_path']);
        if(method_exists($obj, 'finder_config')){
            return call_user_func_array(array($obj, 'finder_config'), array($content));
        }else{
            $render = app::get('search')->render();
            $status = $obj->status($msg);
            if($status){
              //sphinx 配置到sphinx todo
              $tables = $obj->query('show tables');
              foreach($tables as $key=>$row){
                $tablesInfo = $obj->get_describe($row['Index']);
                $column[$row['Index']] = array_combine($tablesInfo['int'],$tablesInfo['int']);
                $setting[$row['Index']] = app::get('search')->getConf('search_index_setting_'.$row['Index']);
              }
              $render->pagedata['setting'] = $setting;
              $render->pagedata['tables'] = $tables;
              $render->pagedata['search_ranker'] =
                array(
                  'proximity_bm25'=>'proximity_bm25',
                  'bm25'=>'bm25',
                  'none'=>'none',
                  'wordcount'=>'wordcount',
                  'proximity'=>'proximity',
                  'matchany'=>'matchany',
                  'fieldmask'=>'fieldmask'
                );
              $render->pagedata['column'] = $column;
            }
            return $render->fetch('config/default.html');
        }
    }

    public function detail_capability($id){
      $render = app::get('search')->render();
    	$content = app::get('base')->model('app_content')->dump(array('content_id'=>$id),'*');
    	$obj = kernel::single($content['content_path']);
        if(method_exists($obj, 'finder_capability')){
            return call_user_func_array(array($obj, 'finder_capability'),array($content));
        }else{
            $status = $obj->status($msg);
            if($status){
              //sphinx 配置到sphinx todo
              $tables = $obj->query('show tables');
              $render->pagedata['tables'] = $tables;
              foreach($tables as $row){
                  $tablesInfo = $obj->query('DESCRIBE '.$row['Index']);
                  $column[$row['Index']] = $tablesInfo;
              }
              $render->pagedata['column'] = $column;
            }
            return $render->fetch('capability/default.html');
        }

    }

    public function column_status($row){
      $server = kernel::single($row[$this->col_prefix.'content_path']);
      $status = $server->status($msg);
      return $msg;
    }

    public function column_used($row)
    {
	     if(app::get('search')->getConf($row[$this->col_prefix.'content_name']) == $row[$this->col_prefix.'content_path']){
	     	 return '<a href="javascript:;" onClick="javascript:W.page(\'index.php?app=search&ctl=search&act=set_default&method=shut&type='.$row[$this->col_prefix.'content_name'].'&name='.$row[$this->col_prefix.'content_path'].'\')" >'.app::get('search')->_('停用').'</a>';
	     }else{
	     	 return '<a href="javascript:;" onClick="javascript:W.page(\'index.php?app=search&ctl=search&act=set_default&method=open&type='.$row[$this->col_prefix.'content_name'].'&name='.$row[$this->col_prefix.'content_path'].'\')" >'.app::get('search')->_('启用').'</a>';
	     }
    }//End Function

    public function column_service_type($row){
    	$serviceObj = kernel::servicelist($row[$this->col_prefix.'content_name']);
    	foreach($serviceObj as $service){
    		if(get_class($service) == $row[$this->col_prefix.'content_path']){
    		    $des = $service->name;
                break;
            }
    	}
        return $des;
    }//End Function

    public function column_description($row){
    	$serviceObj = kernel::servicelist($row[$this->col_prefix.'content_name']);
    	foreach($serviceObj as $service){
    		if(get_class($service) == $row[$this->col_prefix.'content_path']){
    		    $des = $service->description;
                break;
            }
    	}
        return $des;
    }//End Function





}//End Class
