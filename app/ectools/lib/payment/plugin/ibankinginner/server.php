<?php
class ectools_payment_plugin_ibankinginner_server extends ectools_payment_app 
{
     public function callback(&$recv)
    {
        $merid = $this->getConf('mer_id', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));
        $mer_key=$this->getConf('mer_key', substr(__CLASS__, 0, strrpos(__CLASS__, '_')));

        if($this->is_return_valiad($recv, $mer_key))
        {
            $ret['payment_id'] = $recv['v_oid'];
            $ret['account'] = $mer_id;
            $ret['bank'] = app::get('ectools')->_('网银在线');
            $ret['pay_account'] = app::get('ectools')->_('付款帐号');
            $ret['currency'] = $recv['v_moneytype'];
            $ret['money'] = $recv['v_amount'];
            $ret['paycost'] = '0.000';
            $ret['cur_money'] = $recv['v_amount'];
            $ret['trade_no'] = $recv['v_oid'];
            $ret['t_payed'] = time();
            $ret['pay_app_id'] = 'ibankinginner';
            $ret['pay_type'] = 'online';
            $ret['memo'] = $recv['v_pstring'];
            if($recv['v_pstatus'] == '20')
            {
 
                $ret['status'] = 'succ';
            }
            else
            {
                $ret['status'] = 'failed';
            }
        }else{
            $ret['message'] = 'Invalid Sign';
            $ret['status'] = 'invalid';
        }
        return $ret;
    }

    private function is_return_valiad($recv, $key)
    {
        $sign = $this->make_return_sign($recv, $key);
        return ($sign == $recv['v_md5str']) ? true : false;
    }

    private function make_return_sign($recv, $key)
    {
        $linkstring = $recv['v_oid'];
        $linkstring .= $recv['v_pstatus'];
        $linkstring .= $recv['v_amount'];
        $linkstring .= $recv['v_moneytype'];
        $linkstring .= $key;
        return strtoupper(md5($linkstring));
    }
}

