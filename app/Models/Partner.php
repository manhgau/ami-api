<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Helpers\JWT;

class Partner extends Model{

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'is_active',
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

    public function profile()
    {
        return $this->hasOne(PartnerProfile::class,'partner_id', 'id');
    }

    public static function generatePasswordHash($plain_text){
        return  Hash::make($plain_text);
    }

    public static function checkPasswordHash($plain_text, $hashed_password){
        if (Hash::check($plain_text, $hashed_password)) {
            return true;
        }else{
            return false;
        }
    }


    public static function getPartnerByPhone($phone){
        return Partner::where('phone', $phone)->where('is_active',self::IS_ACTIVE)->first();
    }

    public static function getPartnerById($id){
        return Partner::where('id', $id)->first();
    }

    public static function loginAttempByPhone($phone, $password){
        $rs = false;
        $partner = self::getPartnerByPhone($phone);
        if($partner){
            if(self::checkPasswordHash($password, $partner->password??'')){
                $rs = $partner;
            }
        }
        return $rs;
    }

    public static function getPartnerFromAccessToken(){
        $token = request()->header('Authorization');
        $access_token = JWT::checkAccessToken($token);
        if($access_token){
            $aid = $access_token->aid??0;
            $tokenInfo = PartnerAccessToken::where('aid',$aid)->select(['partner_id'])->first();
            if($tokenInfo){
                $partner = $tokenInfo->partner;
                if($partner){
                    return $partner;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * @param $partner
     * @return bool
     */
    public static function isCompletedProfile($partner){
        // return true;    //TODO, for test...
        $profile = $partner->profile;
        if($profile){
            if( $profile->year_of_birth ==0 || $profile->gender==0 ||
                $profile->province_code=='' || $profile->district_code=='' && $profile->ward_code=='' ||
                $profile->academic_level_id==0 || $profile->job_type_id==0 || $profile->marital_status_id==0
            ){
                return false;
            }else{
                return true;
            }
        }else{
            return false;
        }
    }
}
