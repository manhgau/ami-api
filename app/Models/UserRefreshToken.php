<?php

namespace App\Models;

use App\Helpers\JWTClient;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;


class UserRefreshToken extends Model{

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;

    const TYPE_ACCESS_TOKEN = 'access_token';
    const TYPE_REFRESH_TOKEN = 'refresh_token';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'aid',
        'user_id',
        'refresh_expire',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id', 'id');
    }


    public static function generateAccessRefreshToken($user_id){
        //
        $time = time();
        $refresh_expire = config('client_jwt.refresh_ttl')* 60 + $time;
        $aid = JWTClient::createAccessTokenId();
        $access_token = self::__generateAccessToken($user_id, $aid, $time);
        if($access_token){
            $refresh_token = self::__generateRefreshToken($user_id, $aid, $time, $refresh_expire);
            return [
                'access_token'  =>  $access_token,
                'refresh_token' =>  $refresh_token
            ];
        }else{
            return false;
        }
    }
    private static function __generateAccessToken($user_id, $access_token_id, $current_time){
        //
        $time = ($current_time > 0)?$current_time:time();
        $expire = config('client_jwt.ttl') * 60 + $time;
        $access_token = JWTClient::encode([
            'iss'       =>  URL::current(),
            'expire'    =>  $expire,
            'type'      =>  self::TYPE_ACCESS_TOKEN,
            'iss_at'    =>  $time,
            'aid'       =>  $access_token_id,
            'user_id'       =>  $user_id,
            'random'    =>  Str::random(20)
        ]);

        return $access_token;
    }

    private static function __generateRefreshToken($user_id, $aid, $time, $refresh_expire){
        //
        $m = new UserRefreshToken();
        $m->aid = $aid ;
        $m->user_id = $user_id;
        $m->refresh_expire = $refresh_expire;
        if($m->save()){
            $refresh_token = JWTClient::encode([
                'iss'       =>  URL::current(),
                'type'      =>  self::TYPE_REFRESH_TOKEN,
                'iss_at'    =>  $time,
                'aid'       =>  $aid,
                'random'    =>  Str::random(20)
            ]);
            return $refresh_token;
        }else{
            return false;
        }
    }
}
