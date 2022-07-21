<?php

namespace App\Models;

use App\Helpers\JWT;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;


class PartnerAccessToken extends Model{

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;
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


    public static function generateAcessToken($partner_id){
        //
        $time = time();
        $expire = config('partner_jwt.ttl') * 60 + $time;
        $refresh_expire = config('partner_jwt.refresh_ttl')* 60 + $time;;
        $m = new PartnerAccessToken();
        $aid = JWT::createAccessTokenId();
        $m->aid = $aid ;
        $m->partner_id = $partner_id;
        $m->expire = $expire;
        $m->refresh_expire = $refresh_expire;
        if($m->save()){
            $access_token = JWT::encode([
                'iss'       =>  'NguyenPV',
                'iss_at'    =>  $time,
                'aid'       =>  $aid,
                'random'    =>  Str::random(20)
            ]);
            return $access_token;
        }else{
            return false;
        }
    }
}
