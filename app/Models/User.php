<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class User: Tài khoản client
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

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
        'password',
        'active_code',
        'is_active',
        'active_expire',
        'forgot_code',
        'forgot_expire',
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier() {
        return $this->getKey();
    }

    public static function generatePasswordHash($plain_text){
        return bcrypt($plain_text);
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims() {
        return [];
    }

    public static function findUserByEmail($email){
        return User::where('email', $email)->first();
    }

    public static function findUserActiveEmail($user_id, $active_code){
        return User::where('id', $user_id)->whereRaw("BINARY `active_code`= ?",[$active_code])->first();
    }

    public static function findUserForgotPassByEmail($user_id, $forgot_code){
        return User::where('id', $user_id)->whereRaw("BINARY `forgot_code`= ?",[$forgot_code])->first();
    }

    public static function checkUserByEmail($email){
        $count = User::where('email', $email)->count();
        if($count > 0){
            return true;
        }else{
            return false;
        }
    }
}
