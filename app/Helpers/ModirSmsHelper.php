<?php
/**
 * GitHub: RoyalHaze
 * Date: 9/6/24
 * Time: 5:25 PM
 **/

namespace App\Helpers;

use GuzzleHttp\Client;

class ModirSmsHelper
{
    public static function send_otp($to,$code)
    {
        $pattern_id = 'atqxhul5vf';

        $data = [
          'name' => 'کاربر',
          'code' => $code
        ];

        return self::send_pattern($pattern_id,$to,$data);
    }

    public static function send_failed_login($to, $ip, $time, $browser, $os)
    {
        $pattern_id = 'azf7f7frqs3brkv';

        $data = compact('ip', 'time', 'browser', 'os');

        return self::send_pattern($pattern_id, $to, $data);
    }

    public static function send_pattern($pattern_id,$to,$array_data)
    {
        try {
            $data = [
                'sender' => '+983000505',
                'recipient' => $to,
                'code' => $pattern_id,
                'variable' => $array_data
            ];

            $apikey = env('MODIR_SMS_API_KEY','y1_KLPTATmJM2NU86j3LcNWZBWR9OLxeBxlsXvpSvZI=');

            $url = 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send';

            $cl = new Client();

            $res = $cl->post($url,[
                'json' => $data,
                'headers' => [
                    'apikey' => $apikey
                ]
            ]);

            $response = json_decode($res->getBody()->getContents(),true);

            return $response['status'] === 'OK';
        }catch (\Exception $e){
            return false;
        }
    }

    private static function query_builder($data){
        $query = '?';
        $other_data = $data['data'];
        unset($data['data']);
        $is_first = true;
        foreach ($data as $title=>$val){
            if (!$is_first){
                $query = $query.'&';
            }else{
                $is_first = false;
            }
            $query = $query.''.$title.'='.$val;
        }
        $i = 1;
        foreach ($other_data as $title => $val){
            $query = $query.'&p'.$i.'='.$title;
            $query = $query.'&v'.$i.'='.$val;
            $i++;
        }

        return $query;
    }
}
