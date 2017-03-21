<?php
class test extends PHPUnit_Framework_TestCase
{

    public function setUp(){
        $this->model = app::get('search')->model('associate');
        $this->model->delete();
    }

    public function testAssociate(){
        $from_type = 'goods_cat';
        $this->cat_model = app::get('b2c')->model('goods_cat');
        $catSdf = $this->cat_model->getList('cat_id,cat_name');
        foreach($catSdf as $row){
            $filter = array(
                'words' => $row['cat_name'],
                'type_id' => $row['cat_id'],
                'from_type' => 'goods_cat',
                'last_modify' => time(),
            );
            $this->model->save($filter);
        }

        $this->keyword_model = app::get('b2c')->model('goods_keywords');
        $goodsWordsSdf = $this->keyword_model->getList('keyword,goods_id');
        foreach($goodsWordsSdf as $row){
            if($row['keyword']){
                $filter = array(
                    'words' => $row['keyword'],
                    'type_id' => $row['goods_id'],
                    'from_type' => 'goods_keywords',
                    'last_modify' => time(),
                );
                $this->model->save($filter);
            }
        }
    }

}
?>
