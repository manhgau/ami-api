<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyPartnerInputLine extends Model
{
    protected $fillable = [
        'survey_id',
        'partner_input_id',
        'question_id',
        'question_sequence',
        'skipped',
        'answer_type',
        'value_text_box',
        'value_date',
        'value_date_start',
        'value_date_end',
        'value_star_rating',
        'matrix_row_id',
        'matrix_column_id',
        'answer_score',
        'answer_is_correct',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
        'deleted',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    public static  function getALL($perPage = 10,  $page = 1)
    {
        return self::where('deleted', self::NOT_DELETED)->orderBy('id', 'desc')->paginate($perPage, "*", "page", $page)->toArray();
    }



}
