<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class gettext extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
          $this->model = app::get('base')->model('queue');
    }

    public function testGetText(){

        $locale = 'zh_CN'; // Pretend this came from the Accept-Language header
        $locale_dir = PUBLIC_DIR.'/app/site/lang'; // your .po and .mo files should be at $locale_dir/$locale/LC_MESSAGES/messages.{po,mo}

        #setlocale(LC_MESSAGES, $locale);
        setlocale(LC_ALL, $locale);
        putenv("LANGUAGE=$locale");
        $domain = 'lang';
        bindtextdomain($domain, $locale_dir);
        textdomain($domain);
        bind_textdomain_codeset($domain, 'utf-8');
        echo gettext('备案号');
        echo gettext('xiaobai');
    }

}
