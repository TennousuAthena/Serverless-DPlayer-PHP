<?php

require 'vendor/cos-php-sdk-v5/vendor/autoload.php';
require 'config.php';
require 'vendor/tencentcloud-sdk-php/TCloudAutoLoader.php';

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Cms\V20190321\CmsClient;
use TencentCloud\Cms\V20190321\Models\TextModerationRequest;

//初始化
$cosClient = new Qcloud\Cos\Client(array('region' => $region,
    'credentials'=> array(
        'secretId'    => $secret_id,
        'secretKey' => $secret_key)));

//敏感词
function aword($str, $cms=false){
    if(!$str){
        return false;
    }
    if($cms){
        try {
            $cred = new Credential($secret_id, $secret_key);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint("cms.tencentcloudapi.com");
            
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new CmsClient($cred, "ap-guangzhou", $clientProfile);

            $req = new TextModerationRequest();
            
            $params = json_encode(["Content"=>base64_encode($str)]);
            // var_dump($params);
            $req->fromJsonString($params);


            $resp = $client->TextModeration($req);

            $result = json_decode($resp->toJsonString());
            var_dump($result);
        }
        catch(TencentCloudSDKException $e) {
            echo $e;
        }
        if($result->Data->EvilFlag)
            return true;
    }
    $list = require("aword.php");
    for ($i=0; $i<count($list); $i++ ){  
        $content = substr_count($str, base64_decode($list[$i]));  
        if( $content > 0 ){  
            $info = $content;  
            break;  
        }  
    }  
    return $info>0 ? true : false;
}

function main_handler($event, $context) {
    print "start main handler\n";
    print_r($event);
    global $appid;
    global $cosClient;
    global $bucket;
    global $origin;

    $event = (array)$event;
    // print json_encode($event);
    if($event['headers']->origin){
        //生产环境
        //检测域名，如不需要可注释掉
        if(!strpos($event['headers']->origin, $origin)){
            return ["code"=>"0", "msg"=>"Failed to match the origin"];
        }
    }
    
    try {
        //列出Bucket
        $result = $cosClient->listBuckets();
        if(strpos(json_encode((array)$result['Buckets']), "danmaku-".$appid)){
            //bucket存在
        }else{
            //尝试创建bucket
            try {
                $bucket = "danmaku-".$appid; //存储桶名称 格式：BucketName-APPID
                $result = $cosClient->createBucket(array('Bucket' => $bucket));
                //请求成功
                print_r($result);
            } catch (\Exception $e) {
                //请求失败
                die($e);
            }
        }
    } catch (\Exception $e) {
        //请求失败
        die($e);
    }
    if($event['httpMethod'] == "GET"){
        //读取弹幕
        $danmakuFile = "https://".$bucket.".cos.ap-chengdu.myqcloud.com/data/".$event['queryStringParameters']->id.".json";
        $danmakuContent = file_get_contents($danmakuFile);
        if($danmakuContent){
            return(json_decode($danmakuContent));
        }else{
            return ["code"=>0, "data"=>[]];
        }
    }elseif($event['httpMethod'] == "POST"){
        //创建弹幕

        //先看看弹幕文件是否存在
        $body = json_decode($event['body']);
        //敏感词
        if(aword($body->text))
            return ["code"=> -1, "msg"=>"无法发送"];
        if($body->id != ""){
            $danmakuFile = "https://".$bucket.".cos.ap-chengdu.myqcloud.com/data/".$body->id.".json";
            $danmakuContent = file_get_contents($danmakuFile);
            var_dump($danmakuContent);
            $key = "tmp/".$body->id.".json";
            $reqid = (array)$event['headers'];
            $reqid = $reqid['x-api-requestid'];
            $danmaku = ["code"=>0, "data"=>[[
                $body->time, $body->type, $body->color, $reqid, $body->text
            ]]];  //随便生成一个
            if(!$danmakuContent){
                //创建临时文件
                fopen("/".$key, "w");
                if($body->time && $body->color && $body->text){
                    //写下第一条弹幕~~
                    file_put_contents("/".$key, json_encode($danmaku));
                }else{
                    return ["code"=>0, "msg"=> "Danmaku not completed:", $body];
                }
                //上传到Bucket
                try { 
                    $result = $cosClient->putObject(array( 
                        'Bucket' => "danmaku-".$appid,
                        'Key' => "data/".$body->id.".json", 
                        'Body' => fopen("/".$key, 'rb'), 
                    )); 
                    // 请求成功 
                    print_r($result);
                    // $this->versionId = $result['VersionId'];
                    return $danmaku;
                } catch (\Exception $e) { 
                    // 请求失败 
                    echo($e); 
                }
            }else{
                //弹幕文件已经存在了，那就先读取，再合并，再上传吧！
                //创建临时文件
                fopen("/".$key, "w");
                if($body->time && $body->color && $body->text){
                    $newDanmaku = (array)json_decode($danmakuContent);
                    var_dump($danmaku);
                    array_push($newDanmaku["data"], $danmaku['data'][0]);
                    print json_encode($newDanmaku);
                    file_put_contents("/".$key, json_encode($newDanmaku));
                }else{
                    return ["code"=>0, "msg"=> "Danmaku not completed:", $body];
                }
                //上传到Bucket
                try { 
                    $result = $cosClient->putObject(array( 
                        'Bucket' => "danmaku-".$appid,
                        'Key' => "data/".$body->id.".json", 
                        'Body' => fopen("/".$key, 'rb'), 
                    )); 
                    // 请求成功 
                    print_r($result);
                    return $danmaku;
                } catch (\Exception $e) {
                    echo($e); 
                }
            }
        }
    }else{
        //未知请求方式
        return ["code"=>0, "msg"=> "Unkow Request Method"];
    }
    return ["code"=> -1, "msg"=> "Unknown Error"];
}

?>