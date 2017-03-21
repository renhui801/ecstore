<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class goods_save extends PHPUnit_Framework_TestCase
{
    /*
     * author guzhengxiao
     */
    public function setUp()
    {
        $this->model = app::get('b2c')->model('goods');
    }

    public function testInsert(){
        $saveData = array (
            'category' => 
            array (
                'cat_id' => 0,
            ),
            'type' => 
            array (
                'type_id' => '6',
            ),
            'name' => 'CLINIQUE 倩碧 眼部护理水凝霜 15ml',
            'bn' => 'G4CB2BB43BEBC6',
            'brand' => 
            array (
                'brand_id' => '12',
            ),
            'brief' => '',
            'product' => 
            array (
                0 => 
                array (
                    'bn' => 'P4CB2BB143BEBED',
                    'product_id' => '547',
                    'status' => 'true',
                    'weight' => '0.000',
                    'store' => '99',
                    'unit' => '',
                    'is_default' => 'true',
                    'price' => 
                    array (
                        'price' => 
                        array (
                            'price' => '398.000',
                        ),
                        'member_lv_price' => 
                        array (
                            0 => 
                            array (
                                'level_id' => '1',
                                'price' => '',
                            ),
                            1 => 
                            array (
                                'level_id' => '2',
                                'price' => '',
                            ),
                            2 => 
                            array (
                                'level_id' => '3',
                                'price' => '',
                            ),
                            3 => 
                            array (
                                'level_id' => '4',
                                'price' => '',
                            ),
                        ),
                        'cost' => 
                        array (
                            'price' => '0.000',
                        ),
                        'mktprice' => 
                        array (
                            'price' => '',
                        ),
                    ),
                    'goods_id' => '95',
                    'name' => 'CLINIQUE 倩碧 眼部护理水凝霜 15ml',
                ),
            ),
            'images' => 
            array (
                0 => 
                array (
                    'target_type' => 'goods',
                    'image_id' => 'a72a04bb97e08d22f20dd68d5483a89d',
                ),
            ),
            'thumbnail_pic' => false,
            'image_default_id' => 'a72a04bb97e08d22f20dd68d5483a89d',
            'props' => 
            array (
                'p_1' => 
                array (
                    'value' => '74',
                ),
                'p_2' => 
                array (
                    'value' => '77',
                ),
            ),
            'params' => 
            array (
                0 => 
                array (
                    0 => '',
                ),
            ),
            'store' => 99,
            'keywords' => '',
            'unit' => '',
            'weight' => '0.000',
            'status' => 'true',
            'description' => '<p>产品简介</p><p>Clinique (倩碧) 眼部护理水凝霜，清盈的质地迅速渗透入皮肤，实时减轻眼袋、黑眼圈及皱纹的现象，可早晚使用。&nbsp;</p><p></div><div>使用方法</p><p>早晚洁肤后，取适量涂于眼部位置。&nbsp;</p><p></div><div>成份</p><p>抗氧化因子、植物精华、蛋白质、特别保湿分子<span class="Apple-tab-span" style="white-space:pre">	</span></p><p></div><div>适合肤质</p><p>任何皮肤</p>',
            'goods_id' => '95',
            'spec_desc' => NULL,
        );
        $rs = $this->model->save( $saveData );
        if( !$rs ){
            echo '失败';
        }
    }

}
