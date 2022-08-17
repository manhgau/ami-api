<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;

use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\Survey;
use Closure;

class ClientAuthOwnerSurvey
{
    public function handle($request, Closure $next)
    {
        //TODO...
        $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
        $survey_user = Survey::findOrFail($request->id)->where('user_id', $user_id)->first();
        if (!$survey_user) {
            return ClientResponse::response(ClientResponse::$client_auth_owner_survey, 'Khảo sát này không phải của bạn');
        }
        return $next($request);
    }
}
