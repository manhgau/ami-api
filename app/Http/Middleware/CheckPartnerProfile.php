<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;

use App\Helpers\JWT;
use App\Helpers\Context;
use App\Helpers\ClientResponse;
use App\Models\AppSetting;
use App\Models\PartnerAccessToken;
use App\Models\PartnerProfile;
use Closure;

class CheckPartnerProfile
{
    public function handle($request, Closure $next)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        // check đã cập nhập hồ sơ cá nhân chưa
        $partner = $tokenInfo->partner;
        $partner_id = $partner->id ?? 0;
        if (!$this->__a($partner_id)) {
            $survey_profile_id  = AppSetting::getByKey(AppSetting::SURVEY_PROFILE_ID);
            return ClientResponse::response(ClientResponse::$partner_required_fill_info, 'Partner chưa cập nhật hồ sơ cá nhân',  ['survey_profile_id' => $survey_profile_id]);
        }
        return $next($request);
    }
    private function __a($partner_id)
    {
        return PartnerProfile::getDetailPartnerProfile($partner_id);
    }
}
