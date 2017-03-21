<?php

class weixin_qrcode {
     
    //二维码纠错级别
    var $errorCorrectionLevel = 'L'; //L M Q H

    //二维码生成图片大小 1-10
    var $matrixPointSize = 4;

    //生成二维码后缀
    var $extname = '.png';

    //生成图片的临时目录
    var $tmp_dir = TMP_DIR;


    /**
     * 根据需要生成二维码的数据(URL)，生成二维码，并保存到storager
     *
     * $params string $data  可以是URL，文字
     */
    public function store($data, $matrixPointSize=null, $errorCorrectionLevel=null){
        $matrixPointSize = $matrixPointSize ? $matrixPointSize : $this->matrixPointSize;
        $errorCorrectionLevel = $errorCorrectionLevel ? $errorCorrectionLevel : $this->errorCorrectionLevel;

        $imageModel = app::get('image')->model('image');
        $image_id = $this->gen_qrcode_image_id($data, $matrixPointSize, $errorCorrectionLevel);

        if( !$imageModel->getRow('image_id',array('image_id'=>$image_id)) )
        {
            $filename = tempnam($this->tmp_dir,'qrcode');
            weixin_qrcode_QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
            list($w,$h) = getimagesize($filename);

            $storager = new base_storager();
            list($url,$ident,$storage) = explode("|",$storager->save_upload($filename,'image','',$msg,$this->extname));

            $params = array(
                'image_id' => $image_id,
                'storage' => $storage,
                'image_name' => '二维码图片',
                'ident' => $ident,
                'url' => $url,
                'width' => $w,
                'height' => $h,
                'last_modified' => time(),
            );
            $imageModel->save($params);
            unlink($filename);
        }
        return $image_id;
    }

    /**
     * 根据image_id删除二维码图片
     */
    public function remove($image_id){
        $imageModel = app::get('image')->model('image');
        $imageData = $imageModel->getRow('image_id,ident,url,storage',array('image_id'=>$image_id));
        if( !$imageData  ) return true;
        
        //删除storager中存储的图片数据
        $storager = new base_storager();
        $ident_data = $imageData['url'].'|'.$imageData['ident'].'|'.$imageData['storage'];
        $res = $storager->remove($ident_data,'image');
        $imageModel->delete(array('image_id'=>$image_id));
        return true;
    }

    //生成二维码的image_id
    public function gen_qrcode_image_id($data, $matrixPointSize=4, $errorCorrectionLevel='L'){
        return md5($data.$matrixPointSize.$errorCorrectionLevel);
    }

    /**
     * 更新二维码图片
     */
    public function update_goods_qrcode($goods_id){

        $productsModel = app::get('b2c')->model('products');
        $productData = $productsModel->getList('product_id,qrcode_image_id',array('goods_id'=>intval($goods_id)));
        foreach( (array)$productData as $row )
        {
            $this->update_product_qrcode($row);
        }
        return true;
    }

    public function update_product_qrcode($row){
        $productsModel = app::get('b2c')->model('products');
        $qrcodeData = $this->create_qrcode_data( intval($row['product_id']), 'site' );
        if( !$qrcodeData ) return false;
        $qrcode_image_id = $this->store($qrcodeData, $this->matrixPointSize, $this->errorCorrectionLevel);
        if( !empty($row['qrcode_image_id']) && $qrcode_image_id != $row['qrcode_image_id'] ){
            $this->remove($row['qrcode_image_id']);
        }
        $productsModel->update(array('qrcode_image_id'=>$qrcode_image_id), array('product_id'=>intval($row['product_id']) ));
        return true;
    }

    /**
     * 新建二维码，通过货品ID生成二维码返回二维码ID
     *
     * @params int $product_id
     */
    public function insert_product_qrcode($product_id){
        $qrcodeData = $this->create_qrcode_data(intval($product_id), 'site');
        if( !$qrcodeData ) return false;
        $qrcode_image_id = $this->store($qrcodeData, $this->matrixPointSize, $this->errorCorrectionLevel);
        return $qrcode_image_id;
    }

    //生成商品后台管理二维码图片
    public function get_desktop_qrcode_image_id($product_id){
        $qrcodeData = $this->create_qrcode_data(intval($product_id), 'desktop');
        if( !$qrcodeData ) return false;
        $qrcode_image_id = $this->store($qrcodeData, 10, $this->errorCorrectionLevel);
        return $qrcode_image_id;
    }


    /**
     * 根据货品ID生成需要创建二维码的数据
     *
     * @params string $product_id 货品ID
     */
    public function create_qrcode_data($product_id, $type='site'){
        $url = array(
            'app'=>'b2c',
            'ctl'=>'wap_product',
            'full'=>1,
            'arg0'=>$product_id,
        );

        if( app::get('wap')->status() == 'uninstalled' ){
            return false;
        }

        switch( $type ){
            case 'site':
                $data = app::get('wap')->router()->gen_url( $url )."?qr=1";
                break;
            default:
                $data = app::get('wap')->router()->gen_url( $url );
                break;
        }
        return $data;
    }
}
