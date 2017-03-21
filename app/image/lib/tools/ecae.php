<?php
include_lib('image.php');
class image_tools_ecae implements image_interface_tool 
{
    public function resize($src_file, $target_file, $width, $height, $type, $new_width, $new_height) 
    {
        $obj = new ecae_image();
        $obj->set_file($src_file);
        $obj->resize($new_width, $new_height);
        $obj->strip();
        $content = $obj->exec();
        if($content){
            file_put_contents($target_file, $content);
            return true;
        }else{
            return false;
        }                
    }
    public function watermark($file, $mark_image, $set)
    {
        $obj = new ecae_image();
        $obj->set_file($file);
        $obj->watermark(
            file_get_contents($mark_image), $set['dest_x'], $set['dest_y'], 0, 0, $set['wm_opacity']?$set['wm_opacity']:50
            );
        $content = $obj->exec();
        if($content){
            file_put_contents($file, $content);
        }
    }
}