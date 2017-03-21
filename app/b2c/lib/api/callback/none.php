<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_api_callback_none implements b2c_api_callback_interface_app
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function callback($result)
    {
        $str_xml .= "<query>";
        foreach ($result->response as $key=>$value)
        {
            $str_xml .= "<$key>" . $value . "</$key>";
        }
        $str_xml .= "</query>";
        logger::info($str_xml);

        if(is_object($result)){
            $arr_callback_params = $result->get_callback_params();
            $status = $result->get_status();
            $res_message = $result->get_result();
            $data = $result->get_data();
            include_once(ROOT_DIR.'/app/b2c/lib/api/rpc/request_api_method.php');

            $message = 'msg_id:' . $result->response['msg_id'] . ', ' . $arr_apis[$arr_callback_params['method']] . (($status == 'succ') ? app::get('b2c')->_('成功，') : app::get('b2c')->_('失败，')). (($res_message) ? ($res_message.', ') : '') . app::get('b2c')->_('单号：') . $data['tid'];

            $arr_msg = array(
                'rsp' => $status,
                'res' => $message,
                'data' => $data,
            );

            return $arr_msg;
        }
        return true;

    }

    public function response_log($response, $params=array())
    {
        if ($response)
        {
            $response = json_decode($response, 1);
            $obj_rpc_poll = app::get('base')->model('rpcpoll');
            $arr_rpc_id = explode('-', $params['rpc_key']);
            $rpc_id = $arr_rpc_id[0];
            $rpc_calltime = $arr_rpc_id[1];
            $filter = array(
                'id'=>$rpc_id,
                'calltime'=>$rpc_calltime,
            );

            $obj_rpc_poll->update(array('result'=>$response), $filter);
        }
    }
}
