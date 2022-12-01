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
        $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
        $id = $request->id ?? ($request->survey_id ?? '');
        $survey_user = Survey::getDetailSurveyByUser($id, $user_id);
        if (!$survey_user) {
            return ClientResponse::responseError('Không có bản ghi phù hợp');
        }
        return $next($request);
    }
}
