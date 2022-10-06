<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogicAnswers extends Model
{
    protected $fillable = [
        'question_id',
        'survey_id',
        'answer_id',
        'sequence_logic',
        'question_type	',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted'
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'updated_by', 'created_by'];

    public static  function insertLogic($data)
    {
        return self::insertOrIgnore($data);
    }
}
