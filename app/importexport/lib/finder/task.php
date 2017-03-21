<?php
class importexport_finder_task{

    var $column_control = '操作';

    public function column_control($row){
        $value = app::get('importexport')->model('task')->getList('*',array('task_id'=>$row['task_id']));
        if($value[0]['status'] == '2'){
            $href = 'index.php?app=importexport&ctl=admin_export&act=queue_download&task_id='.$row['task_id'].'&finder_id='.$_GET['_finder']['finder_id'];
        }elseif($value[0]['status'] == '6' || $value[0]['status'] == '8'){
            $href = 'index.php?app=importexport&ctl=admin_import&act=queue_download&task_id='.$row['task_id'].'&finder_id='.$_GET['_finder']['finder_id'];
        }
        if($href){
            $returnValue = "<a href='$href' onclick='location.href="."\"$href\""."'>".app::get('importexport')->_("下载")."</a>";
        }
        return $returnValue;
    }

}
