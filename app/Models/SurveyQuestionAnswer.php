<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestionAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'matrix_question_id',
        'sequence',
        'value',
        'is_correct',
        'answer_score',
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'updated_by', 'created_by'];

    public static  function SurveyQuestionAnswer($data)
    {
        return self::create($data);
    }

    public static  function getAllSurveyQuestionAnswer($id)
    {
        return self::where('question_id', $id);
    }

    public static  function getDetailSurveyQuestionAnswer( $id)
    {
        return self::where('id', $id)->first();
    }

    public static  function updateSurveyQuestionAnswer($data, $id)
    {
        return self::where('id', $id)->update($data);
    }

    public static  function deleteSurveyQuestionAnswer( $id)
    {
        return self::where('question_id', $id)->orWhere('matrix_question_id', $id)->delete();
    }



}
