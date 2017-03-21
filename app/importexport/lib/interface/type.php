<?php

interface importexport_interface_type{

     /**
      * 将导出的数组转换为导出的格式，
      * 约定：在每次转换后最后在此函数换行(循环调用此函数进行写文件)
      *       在将转换后的字符串写到文件中则不进行换行操作
      *
      * @params $data array 需要导入的数组，一维数组
      * @return $rs string 转换后的格式
      *
      * @return rs
      */
     public function arrToExportType($data);

    /**
     * 返回获取的文件数据
     *
     * @param $handle 打开的文件句柄
     * @param $contents 获取到的数据
     * @param $line 行数 
     */
    public function fgethandle(&$handle, &$contents, $line);
}
