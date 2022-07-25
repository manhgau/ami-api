<?php

namespace App\Models;

use App\Helpers\JWT;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;


class PartnerAccessToken extends Model{

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
        'partner_id',
        'expire',
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

    public function partner()
    {
        return $this->belongsTo(Partner::class,'partner_id', 'id');
    }


    public static function generateAccessRefreshToken($partner_id){
        //
        $time = time();
        $expire = config('partner_jwt.ttl') * 60 + $time;
        $refresh_expire = config('partner_jwt.refresh_ttl')* 60 + $time;
        $aid = JWT::createAccessTokenId();
        $access_token = self::__generateAccessToken($partner_id, $aid, $time, $expire, $refresh_expire);
        if($access_token){
            $refresh_token = self::__generateRefreshToken($aid, $time);
            return [
                'access_token'  =>  $access_token,
                'refresh_token' =>  $refresh_token
            ];
        }else{
            return false;
        }
    }

    private static function __generateAccessToken($partner_id, $aid, $time, $expire, $refresh_expire){
        //
        $m = new PartnerAccessToken();
        $m->aid = $aid ;
        $m->partner_id = $partner_id;
        $m->expire = $expire;
        $m->refresh_expire = $refresh_expire;
        if($m->save()){
            $access_token = JWT::encode([
                'iss'       =>  URL::current(),
                'type'      =>  self::TYPE_ACCESS_TOKEN,
                'iss_at'    =>  $time,
                'aid'       =>  $aid,
                'random'    =>  Str::random(20)
            ]);
            return $access_token;
        }else{
            return false;
        }
    }

    private static function __generateRefreshToken($access_token_id, $current_time){
        //
        $time = ($current_time > 0)?$current_time:time();
        $refresh_token = JWT::encode([
            'iss'       =>  URL::current(),
            'type'      =>  self::TYPE_REFRESH_TOKEN,
            'iss_at'    =>  $time,
            'aid'       =>  $access_token_id,
            'random'    =>  Str::random(20)
        ]);

        return $refresh_token;
    }
}
