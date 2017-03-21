<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */

/*基础配置项*/
$setting['author']='tylerchao.sh@gmail.com';
$setting['version']='v1.0';
$setting['name']='首页主商品分类';
$setting['order']=0;
$setting['stime']='2013-07';
$setting['catalog']='商品相关';
$setting['description'] = '支持三级分类展示；支持不弹出状态下展示二级分类；支持关联促销和品牌信息；尺寸可视化编辑；支持左右方向弹出; 经过千个以上分类性能测试';
$setting['userinfo'] = '';
$setting['usual']    = '1';
$setting['vary'] =
$setting['tag']    = 'auto';
$setting['template'] = array(
                            'default.html'=>app::get('b2c')->_('默认')
                        );

/*首次默认配置项*/
$setting['sub_category_width'] = 417;  //%
$setting['brands_box_width']= 191; //px
$setting['brand_width'] 	= 75;//px
$setting['brand_height'] 	= 42;//px
$setting['sales_title'] 	= "相关促销";
$setting['brand_title'] 	= "相关品牌";
$cur_url = $_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'];
$setting['vary'] = $cur_url;
?>
