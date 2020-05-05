<?php
/*
 * Copyright (c) 2017-2018 THL A29 Limited, a Tencent company. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace TencentCloud\Trtc\V20190722;

use TencentCloud\Common\AbstractClient;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Credential;
use TencentCloud\Trtc\V20190722\Models as Models;

/**
 * @method Models\DescribeCallDetailResponse DescribeCallDetail(Models\DescribeCallDetailRequest $req) 查询指定时间内的用户列表及用户通话质量数据。
 * @method Models\DescribeHistoryScaleResponse DescribeHistoryScale(Models\DescribeHistoryScaleRequest $req) 查询历史房间和用户数，每分钟1次，可查询最近5天的数据
 * @method Models\DescribeRealtimeNetworkResponse DescribeRealtimeNetwork(Models\DescribeRealtimeNetworkRequest $req) 查询sdkappid维度下实时网络状态，包括上行丢包与下行丢包。可查询24小时内数据，查询起止时间不超过1个小时。
 * @method Models\DescribeRealtimeQualityResponse DescribeRealtimeQuality(Models\DescribeRealtimeQualityRequest $req) 查询sdkappid维度下实时质量数据，包括：进房成功率，首帧秒开率，音频卡顿率，视频卡顿率。可查询24小时内数据，查询起止时间不超过1个小时。
 * @method Models\DescribeRealtimeScaleResponse DescribeRealtimeScale(Models\DescribeRealtimeScaleRequest $req) 查询sdkappid维度下实时规模，可查询24小时内数据，查询起止时间不超过1个小时。
 * @method Models\DescribeRoomInformationResponse DescribeRoomInformation(Models\DescribeRoomInformationRequest $req) 查询sdkappid下的房间列表。默认返回10条通话，一次最多返回100条通话。可查询最近5天的数据。
 * @method Models\DismissRoomResponse DismissRoom(Models\DismissRoomRequest $req) 接口说明：把房间所有用户从房间移出，解散房间。支持所有平台，Android、iOS、Windows 和 macOS 需升级到 TRTC SDK 6.6及以上版本。
 * @method Models\RemoveUserResponse RemoveUser(Models\RemoveUserRequest $req) 接口说明：将用户从房间移出，适用于主播/房主/管理员踢人等场景。支持所有平台，Android、iOS、Windows 和 macOS 需升级到 TRTC SDK 6.6及以上版本。
 */

class TrtcClient extends AbstractClient
{
    /**
     * @var string
     */
    protected $endpoint = "trtc.tencentcloudapi.com";

    /**
     * @var string
     */
    protected $version = "2019-07-22";

    /**
     * @param Credential $credential
     * @param string $region
     * @param ClientProfile|null $profile
     * @throws TencentCloudSDKException
     */
    function __construct($credential, $region, $profile=null)
    {
        parent::__construct($this->endpoint, $this->version, $credential, $region, $profile);
    }

    public function returnResponse($action, $response)
    {
        $respClass = "TencentCloud"."\\".ucfirst("trtc")."\\"."V20190722\\Models"."\\".ucfirst($action)."Response";
        $obj = new $respClass();
        $obj->deserialize($response);
        return $obj;
    }
}
