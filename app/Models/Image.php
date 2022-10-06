<?php

namespace App\Models;

use App\Helpers\Common\CommonCached;
use Illuminate\Database\Eloquent\Model;


class Image extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'path',
        'name',
        'size',
        'upload_ip',
        'created_at',
    ];
}
