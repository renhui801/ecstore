<?php

interface search_interface_search
{
    public function link();

    public function select($queryArr=array());

    public function insert($val=array());

    public function update($val=array(),$where);

    public function delete($val=array());

    public function reindex(&$msg);

    public function optimize(&$msg);

    public function status(&$msg);

    public function clear(&$msg);
}
