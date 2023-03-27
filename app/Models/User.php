<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Helpers\JWTClient;

/**
 * Class User: Tài khoản client
 * @package App\Models
 */
class User extends Model
{

    const IS_ACTIVE = 1;
    const IS_NOT_ACTIVE = 0;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'job_type_id',
        'business_scope_id',
        'password',
        'active_code',
        'is_active',
        'active_expire',
        'forgot_code',
        'forgot_expire',
        'logo',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getAllUser()
    {
        return self::where('is_active', self::IS_ACTIVE)->get()->toArray();
    }


    public static function generatePasswordHash($plain_text)
    {
        return  Hash::make($plain_text);
    }

    public static function checkPasswordHash($plain_text, $hashed_password)
    {
        if (Hash::check($plain_text, $hashed_password)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    const LOGO                          = 'logo';
    const AVATAR                        = 'avatar';


    public static function getTypeImage()
    {
        return [
            self::LOGO,
            self::AVATAR,
        ];
    }
    public static function checkImageValid($type)
    {
        $list = self::getTypeImage();
        if (in_array($type, $list)) {
            return true;
        } else {
            return false;
        }
    }
    public static  function updateProfile($data, $id)
    {
        return self::where('id', $id)->update($data);
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public static function findUserActiveEmail($user_id, $active_code)
    {
        return User::where('id', $user_id)->whereRaw("BINARY `active_code`= ?", [$active_code])->first();
    }

    public static function findUserForgotPassByEmail($user_id, $forgot_code)
    {
        return User::where('id', $user_id)->whereRaw("BINARY `forgot_code`= ?", [$forgot_code])->first();
    }

    public static function checkUserByEmail($email)
    {
        $count = User::where('email', $email)->count();
        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function loginAttempByEmail($email, $password)
    {
        $rs = false;
        $user = self::findUserByEmail($email);
        if ($user) {
            if (self::checkPasswordHash($password, $user->password ?? '')) {
                $rs = $user;
            }
        }
        return $rs;
    }

    public static function getUserFromAccessToken()
    {
        $token = request()->header('Authorization');
        $access_token = JWTClient::checkAccessToken($token);
        if ($access_token) {
            $aid = $access_token->aid ?? 0;
            $tokenInfo = UserRefreshToken::where('aid', $aid)->select(['user_id'])->first();
            if ($tokenInfo) {
                $user = $tokenInfo->user;
                if ($user) {
                    return $user;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
