<?php

namespace nulastudio\Networking\Http;

class UserAgent
{
    const USER_AGENTS = [
        'WIN10_X64_IE11'   => 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
        'WIN10_X64_EDGE'   => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14379',
        'WIN10_X64_CHROME' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36',
        'WM10_X64_EDGE'    => 'Mozilla/5.0 (Windows Phone 10.0; Android 6.0.1; Microsoft; RM-1113) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Mobile Safari/537.36 Edge/14.14379',
        'IE6'              => 'Mozilla/4.0 (Windows; MSIE 6.0; Windows NT 5.2)',
        'IE7'              => 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)',
        'IE8'              => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)',
        'IE9'              => 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)',
        'IE10'             => 'Mozilla/5.0 (compatible; WOW64; MSIE 10.0; Windows NT 6.2)',
        'IOS7_SAFARI'      => 'mozilla/5.0 (iphone; cpu iphone os 7_0_2 like mac os x) applewebkit/537.51.1 (khtml, like gecko) version/7.0 mobile/11a501 safari/9537.53',
        'NEXUS10_CHROME'   => 'Mozilla/5.0 (Linux; Android 4.2.0; Nexus 10 Build/JOP24G) AppleWebKit/537.51.1 (KHTML, like Gecko) Chrome/51.0.2704.79 Mobile Safari/537.36',
        'IOS10'            => 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_3 like Mac OS X) AppleWebKit/603.3.8 (KHTML, like Gecko) Mobile/14G60 MicroMessenger/6.6.1 NetType/WIFI Language/zh_HK',
        'MACOSX10.13.2'    => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/604.4.7 (KHTML, like Gecko) Version/11.0.2 Safari/604.4.7',
    ];

    public static function random()
    {
        $count = count(self::USER_AGENTS);
        return array_values(self::USER_AGENTS)[mt_rand(0, $count - 1)];
    }
}
