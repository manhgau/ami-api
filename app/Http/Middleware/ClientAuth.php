<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;

use App\Helpers\JWTClient;
use App\Helpers\Context;
use App\Helpers\ClientResponse;
use App\Models\UserRefreshToken;
use Closure;

class ClientAuth
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');
        $access_token = JWTClient::checkAccessToken($token);
        $type = $access_token->type ?? '';
        if ($access_token && $type == UserRefreshToken::TYPE_ACCESS_TOKEN) {
            $expire = $access_token->expire??0;
            if($expire > time()) {
                $user_id = $access_token->user_id ?? 0;
                Context::getInstance()->set(Context::CLIENT_USER_ID, $user_id);
                return $next($request);
            }else{
                return ClientResponse::response(ClientResponse::$required_refresh_token, 'Access token đã hết hạn');
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
