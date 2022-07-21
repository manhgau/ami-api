<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 21/07/2022
 * Time: 13:48
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpLog extends Model
{
    const SENT_SUCCESS = 1;
    const SENT_ERROR = 0;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone',
        'otp',
        'sent',
        'expire_at',
        'created_at',
        'updated_at',
        'note',
    ];
}