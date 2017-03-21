<?php
class b2c_apiv_apis_response_goods_brand
{
    private $list = 'brand_id,brand_name,brand_url,brand_desc,brand_logo,ordernum,brand_keywords';
    private $count = null;
    //公开构造函数
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 根据brand_id，获取品牌详细信息
     * @param brand_name string 搜索名称
     * @param brand_id string brand_id(可以为多个)数组的json格式
     * @param page_no int 第几页
     * @param page_size int 每页容量
     * @return brand_id 品牌编号
     * @return brand_name 品牌名称
     * @return brand_url 品牌公司网站
     * @return brand_desc 品牌详细信息
     * @return brand_alias 别名
     * @return image 品牌图片链接（包括大中小图）
     */
    public function get_brand_detail($params,$services)
    {
        $filter = array('disabled'=>'false');
        if(isset($params['brand_id']) && $params['brand_id']!=null)
        {
            $json_brand_ids = $params['brand_id'];
            $brand_ids = json_decode($json_brand_ids, true);
            if(isset($brand_ids) && count($brand_ids) <= 20 && count($brand_ids) >= 1)
            {
                $filter['brand_id|in'] = $brand_ids;
            }
            elseif(count($brand_ids) > 20)
            {
                return array('status'=>null,'massage'=>'请输入20个以下的品牌编号');
            }
        }
        $page_no = $params['page_no'] >= 1 ? $params['page_no'] : 1;
        $page_size = $params['page_num'] ? $params['page_num'] : 20;
        $offset = ($page_no - 1) * $page_size;
        $limit = $page_size;
        if(isset($params['brand_name']) && $params['brand_name']!=null)
        {
            $filter['brand_name'] = $params['brand_name'];
        }
        return array('brandData'=>$this->get_brand($filter, $offset, $limit),'total'=>$this->count);
    }

    private function get_brand($filter, $offset = 0, $limit = 20)
    {
        $obj_brand = $this->app->model('brand');
        $data = $obj_brand->getList($this->list, $filter, $offset, $limit, ' ordernum ');
        $this->count = $obj_brand->count($filter);
        return $this->get_ret_info($data);
    }

    //组织数据
    private function get_ret_info($data)
    {
        if($data==null || count($data)<1) return $data;
        foreach($data as $d)
        {
            $d['brand_alias'] = explode('|',$d['brand_keywords']);
            unset($d['brand_keywords']);
            $fmt_brand[$d['brand_id']] = $d;
            $image_ids[$d['brand_logo']] = $d['brand_logo'];
        }
        $fmt_image = $this->image_ids_to_urls($image_ids);
        foreach($fmt_brand as $key=>$value)
        {
            $fmt_brand[$key]['image'] = $fmt_image[$value['brand_logo']];
            unset($fmt_[$key]['brand_logo']);
            
        }
        return $fmt_brand;
    }
    
    //将图片id转化为图片地址
    private function image_ids_to_urls($image_ids)
    {
        if($image_ids==null || count($image_ids)<1) return $image_ids;
        $obj_image = app::get('image')->model('image');
        $resource_host_url = kernel::get_resource_host_url();
        $image_from_db = $obj_image->getList('image_id,storage,l_url,m_url,s_url',array('image_id|in'=>$image_ids));
        foreach($image_from_db as $imageRow)
        {
            $image_id = $imageRow['image_id'];
            $fmt_image[$image_id]['s_url'] = $imageRow['s_url'] ? $imageRow['s_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['s_url'] &&!strpos($fmt_image[$image_id]['s_url'],'://')){
                $fmt_image[$image_id]['s_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['s_url'];
            }
            $fmt_image[$image_id]['m_url'] = $imageRow['m_url'] ? $imageRow['m_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['m_url'] &&!strpos($fmt_image[$image_id]['m_url'],'://')){
                $fmt_image[$image_id]['m_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['m_url'];
            }
            $fmt_image[$image_id]['l_url'] = $imageRow['l_url'] ? $imageRow['l_url'] : $imageRow['url'];
            if($fmt_image[$image_id]['l_url'] &&!strpos($fmt_image[$image_id]['l_url'],'://')){
                $fmt_image[$image_id]['l_url'] = $resource_host_url.'/'.$fmt_image[$image_id]['l_url'];
            }
        }       
        return $fmt_image;
    }
}
