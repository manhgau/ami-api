<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 25/10/2022
 * Time: 09:05
 */


namespace App\Http\Middleware;

use App\Helpers\RedisLogRequestResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Helpers\Context;

class ClientRequestLogging
{

    public function handle(Request $request, Closure $next)
    {
        $is_debug = env('LOG_DEBUG');
        if ($is_debug) {
            $request_id = Str::uuid();
            Context::getInstance()->set(Context::REQUEST_ID, $request_id);
            $dateString = now()->format('Y-m-d H:i:s');
            $req = [
                'date' => $dateString,
                'ip' => $request->ip(),
                'request_uri' => $request->path(),
                'request_params' => $request->all(),
                'request_header' => $request->headers->all(),
            ];

            RedisLogRequestResponse::store($request_id, $req, RedisLogRequestResponse::WEB_KEY, RedisLogRequestResponse::LOG_REQUEST_KEY);

        }
        return $next($request);
    }
}