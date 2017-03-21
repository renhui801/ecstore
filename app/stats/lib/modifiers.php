<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class stats_modifiers
{
    /**
     * 构造方法，初始化此类的某些对象
     * @param object 此应用的对象
     * @return null
     */
    public function __construct($app)
    {
       $this->app = $app;
    }

    /**
     * 抛出数据给rpc，抛出点页面的底部
     * @param null
     * @return null
     */
    public function print_footer()
    {
        /**
         * 得到页面的属性，app，controller and action
         */
        $obj_base_component_request = kernel::single('base_component_request');
        $ctl_name = $obj_base_component_request->get_ctl_name();
        $act_name = $obj_base_component_request->get_act_name();
        $app_name = $obj_base_component_request->get_app_name();
        /**
         * app version
         */
        $obj_apps = app::get('base')->model('apps');
        $rows = $obj_apps->getList('local_ver', array('app_id' => $app_name));
        $app_ver = $rows[0]['local_ver'];

        $RSC_RPC = $this->app->getConf('site.rsc_rpc.url');

        // 开始抛出符合条件数据
        if ($this->app->getConf('site.rsc_rpc') && $RSC_RPC && ($certificate = base_certificate::certi_id()))
        {
            $RSC_RPC_STR = '<script>
            withBrowserStore(function(store){
                function randomChar(l)
                {
                    var  x="0123456789qwertyuioplkjhgfdsazxcvbnm";
                    var  tmp="";
                    for(var i=0;i<  l;i++)
                    {
                    tmp  +=  x.charAt(Math.ceil(Math.random()*100000000)%x.length);
                    }
                    return  tmp;
                }

                var lf = decodeURI(window.location.href);
                var pagetitle = document.title;
                var new_hs = "";
                var pos = lf.indexOf("#r-");
                var pos2 = lf.indexOf("%23r-");
                var stats = "";
                var rsc_rpc_url = "' . $RSC_RPC . '";
                var certi_id = "' . $certificate . '";
                var js_session_id = "' . md5(md5(kernel::single("base_session")->sess_id())) . '";
                var page_type = "' . urlencode($app_name) . ':' . urlencode($ctl_name) . ':' . urlencode($act_name) . '";
                var app_version = "ecos_'.$app_name.'('.$app_ver.')";
                var rstimestamp = "'.time().'";

                if(pos!=-1||pos2!=-1)
                {
                    if(pos2!=-1){
                    pos=pos2+2;
                    }
                    new_hs=lf.substr(pos+1);
                }

                var old_hs = Cookie.read("S[SHOPEX_ADV_HS]");
                if(new_hs && old_hs!=new_hs)
                {
                    Cookie.set("S[SHOPEX_ADV_HS]",new_hs);
                }

                var shopex_stats = JSON.decode(Cookie.read("S[SHOPEX_STATINFO]"));
                if (shopex_stats)
                {
                    Cookie.write("S[SHOPEX_STATINFO]","",{path:"/"});
                }

                Object.each(shopex_stats,function(value, key){
                    stats += "&" + key + "=" + value;
                });

                if (!stats && Browser.Plugins.Flash.version)
                {
                    new Request({
                        url:"' . kernel::router()->gen_url(array('app'=>'stats', 'ctl'=>'site_statsajax', 'act'=>'index')) . '",
                        method:"post",
                        data:"method=getkvstore",
                        onSuccess:function(response){
                            response = JSON.decode(response);
                            var res = response;
                            if (res){
                                page_type="order:index";
                            }
                            Object.each(res,function(value, key){
                                stats += "&" + key + "=" + value;
                            });

                            store.get("jsapi",function(data){
                                var script = document.createElement("script");

                                var _src = rsc_rpc_url + "/jsapi?certi_id="+certi_id+"&_dep="+js_session_id+"&pt=" + page_type + "&app="+app_version+"&uid="+(encodeURIComponent(Cookie.read("S[MEMBER]") || "").split("-")[0])+"&ref="+encodeURIComponent(document.referrer)+"&sz="+JSON.encode(window.getSize())+"&hs="+encodeURIComponent(Cookie.read("S[SHOPEX_ADV_HS]") || new_hs)+"&rt="+ rstimestamp + stats + "&_pagetitle=" + pagetitle;
                                if(data){
                                    try{
                                    data = JSON.decode(data);
                                    }catch(e){}

                                    if($type(data)=="object"){
                                        _src +="&"+Hash.toQueryString(data);
                                    }else if($type(data)=="string"){
                                        _src +="&"+data;
                                    }
                                }

                                script.setAttribute("src",_src);
                                document.head.appendChild(script);

                            });
                        }
                    }).send();
                }
                else
                {
                    store.get("jsapi",function(data){
                        var script = document.createElement("script");

                        var _src = rsc_rpc_url + "/jsapi?certi_id="+certi_id+"&_dep="+js_session_id+"&pt=" + page_type + "&app="+app_version+"&uid="+(encodeURIComponent(Cookie.read("S[MEMBER]") || "").split("-")[0])+"&ref="+encodeURIComponent(document.referrer)+"&sz="+JSON.encode(window.getSize())+"&hs="+encodeURIComponent(Cookie.read("S[SHOPEX_ADV_HS]") || new_hs)+"&rt="+ rstimestamp + stats + "&_pagetitle=" + pagetitle;
                        if(data){
                            try{
                            data = JSON.decode(data);
                            }catch(e){}

                            if($type(data)=="object"){
                                _src +="&"+Hash.toQueryString(data);
                            }else if($type(data)=="string"){
                                _src +="&"+data;
                            }
                        }

                        script.setAttribute("src",_src);
                        document.head.appendChild(script);
                    });
                }

            });
            </script>';
        }

        return $RSC_RPC_STR;
    }
}

?>