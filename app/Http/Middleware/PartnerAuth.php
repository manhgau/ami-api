<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;
use App\Helpers\JWT;
use App\Helpers\Context;
use App\Helpers\ClientResponse;
use App\Models\PartnerAccessToken;
use Closure;

class PartnerAuth{
    private $__except_check_expire = ['App\Http\Controllers\v1\partner\AuthController@refresh'];

    public function handle($request, Closure $next){
        $token = $request->header('Authorization');
        $access_token = JWT::checkAccessToken($token);
        if($access_token){
            $aid = $access_token->aid??0;
            $tokenInfo = PartnerAccessToken::where('aid',$aid)->first();
            if($tokenInfo){
                //
                $currentAction = \Route::currentRouteAction();
                $time = time();
                $expire = $tokenInfo->expire??0;
                $refresh_expire = $tokenInfo->refresh_expire??0;
                if($expire < $time && !in_array($currentAction, $this->__except_check_expire)){
                    return ClientResponse::response(ClientResponse::$required_refresh_token, 'Gọi api refresh token');
                }else if($refresh_expire < $time){
                    return ClientResponse::response(ClientResponse::$required_login_code, 'Bạn cần đăng nhập để thực hiện chức năng này');
                }else{
                    Context::getInstance()->set(Context::PARTNER_ACCESS_TOKEN,$tokenInfo) ;
                    return $next($request);
                }
            }else{
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }else{
            return ClientResponse::response(ClientResponse::$required_login_code, 'Yêu cầu truy cập bị từ chối');
        }
    }
}
