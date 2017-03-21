<?php
class starbuy_special_count {
    //获取用户购买数量
    public function get_member_count($member_id, $product_id)
    {
        $mdl_count_member_buy = app::get('starbuy')->model('count_member_buy');
        $filter = array(
            'member_id'=>$member_id,
            'product_id'=>$product_id,
        );
        $ret = $mdl_count_member_buy->getRow('cid,count,member_id,product_id', $filter);
        return $ret;
    }

    //用于释放订单
    public function cut_count($special_id, $member_id, $product_id, $cut_count)
    {
        $mdl_count_member_buy = app::get('starbuy')->model('count_member_buy');
        $filter = array(
            'member_id'=>$member_id,
            'special_id'=>$special_id,
            'product_id'=>$product_id,
        );
        $sdf = $mdl_count_member_buy->getRow('*', $filter);
        if($sdf == null)
        {
            return true;
        }else{
            $sdf['count'] = $sdf['count'] - $cut_count;
        }
        if($sdf['count'] < 0)
        {
            $sdf['count'] = 0;
        }
        $ret = $mdl_count_member_buy->save($sdf);
        return $ret;
    }

    //添加用户购买数量
    public function add_count($member_id, $product_id, $add_count)
    {
        $special_info = $this->get_special_info($product_id);
        $special_id = $special_info['special_id'];

        $mdl_count_member_buy = app::get('starbuy')->model('count_member_buy');
        $filter = array(
            'special_id'=>$special_id,
            'member_id'=>$member_id,
            'product_id'=>$product_id,
        );
        $sdf = $mdl_count_member_buy->getRow('*', $filter);
        if($sdf == null)
        {
            $sdf = array(
                'special_id'=>$special_id,
                'member_id'=>$member_id,
                'product_id'=>$product_id,
                'count'=>$add_count,
            );
        }else{
            $sdf['count'] = $sdf['count'] + $add_count;
        }
        $ret = $mdl_count_member_buy->save($sdf);
        return $ret;
    }
    
    //获取活动信息
    public function get_special_info($product_id, $columns='special_id', $time=null)
    {
        if($time == null)
        {
            $time = time();
        }
        $mdl_special_goods = app::get('starbuy')->model('special_goods');
        $special_goods_filter = array(
            'product_id' => $product_id,
            'end_time|bthan'=>$time,
            'begin_time|sthan'=>$time,
        );
        $special_goods = $mdl_special_goods->getRow($columns, $special_goods_filter);
        return $special_goods;
    }

    //检查用户是否可以购买
    public function check_count($member_id, $product_id, $number)
    {
        $special_info = $this->get_special_info($product_id, 'special_id,`limit`');
        $special_id = $special_info['special_id'];
        $limit = $special_info['limit'];
        $member_buy_info = $this->get_member_count($member_id, $product_id);
        $count = $member_buy_info['count'];
        if($count + $number <= $limit)
        {
            return true;
        }else{
            return false;
        }
    }

    //批量获取活动货品
    public function get_special_products($fmt_check_products, $sdf_time=null)
    {
        if($sdf_time == null)
        {
            $time = time();
        }else{
            $time = $sdf_time;
        }
        foreach($fmt_check_products as $product_id=>$num)
        {
            $product_ids[$product_id] = $product_id;
        }
        $mdl_special_goods = app::get('starbuy')->model('special_goods');
        $special_goods_filter = array(
            'product_id|in' => $product_ids,
            'end_time|bthan'=>$time,
            'begin_time|sthan'=>$time,
        );
        $columns = 'special_id,`limit`,product_id';
        $special_goods = $mdl_special_goods->getList($columns, $special_goods_filter);
        foreach($special_goods as $v)
        {
            $fmt_special_id[$v['special_id']] = $v['special_id'];
            $fmt_special_goods[$v['special_id']] = $v;
        }
        if($sdf_time == null)
        {
            $mdl_special = app::get('starbuy')->model('special');
            $specials = $mdl_special->getList('special_id,status', array('special_id|in'=>$fmt_special_id));
            foreach($specials as $s)
            {
                if($s['status']=='false')
                {
                    unset($fmt_special_goods[$s['special_id']]);
                }
            }
        }
        return $fmt_special_goods;
    }
}

