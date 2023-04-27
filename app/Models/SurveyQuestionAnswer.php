<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyQuestionAnswer extends Model
{
    protected $fillable = [
        'survey_id',
        'question_id',
        'matrix_question_id',
        'sequence',
        'logic_come',
        'value',
        'value_type',
        'is_correct',
        'answer_score',
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
    const NOT_LOGIC  = 0;

    const ROW  = 'row';
    const COLUMN  = 'column';

    protected $hidden = ['deleted', 'created_at', 'updated_at', 'updated_by', 'created_by'];

    public static  function SurveyQuestionAnswer($data)
    {
        return self::create($data);
    }

    public static  function getLogicAnswer($survey_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('survey_id', $survey_id)->where('logic_come', '<>', self::NOT_LOGIC)->get()->toArray();
    }

    public static  function deleteAllSurveyQuestionsAnswer($survey_id, $question_id = null)
    {
        $query =  self::where('survey_id', $survey_id);
        if ($question_id != null) {
            $query->where('question_id', $question_id)
                ->orWhere('matrix_question_id', $question_id);
        }
        return $query->delete();
    }

    public static  function getAllSurveyQuestionAnswer($id,  $random = 0, $sort_alphabetically = 0)
    {
        $query = self::select('id', 'question_id', 'matrix_question_id', 'sequence', 'logic_come', 'value', 'value_type')
            ->where('deleted', self::NOT_DELETED)
            ->where(function ($query) use ($id) {
                $query->where('question_id', $id)
                    ->orWhere('matrix_question_id', $id);
            });
        if ($random == 1) {
            $query = $query->inRandomOrder();
        }
        if ($sort_alphabetically == 1) {
            $query = $query->orderBy('value', 'asc');
        } else {
            $query->orderBy('sequence', 'asc');
        }
        return $query;
    }

    public static  function getAnswerMatrixRow($question_id)
    {
        return self::where('question_id', $question_id)->orderBy('sequence', 'asc')->get();
    }

    public static  function getAllAnswer($question_id, $value_type = null)
    {
        $query = self::where('deleted', self::NOT_DELETED)
            ->where(function ($query) use ($question_id) {
                $query->where('question_id', $question_id)
                    ->orWhere('matrix_question_id', $question_id);
            });
        if ($value_type != null) {
            $query = $query->where('value_type', $value_type);
        };
        return  $query->orderBy('sequence', 'asc')->get()->toArray();
    }

    public static  function getAllAnswerStatistic($question_id, $value_type = null)
    {
        $query = self::where(function ($query) use ($question_id) {
            $query->where('question_id', $question_id)
                ->orWhere('matrix_question_id', $question_id);
        });
        if ($value_type != null) {
            $query = $query->where('value_type', $value_type);
        };
        return  $query->orderBy('sequence', 'asc')->get()->toArray();
    }

    public static  function getDetailSurveyQuestionAnswer($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->first();
    }

    public static  function getDetailSurveyQuestionAnswerStatisti($id)
    {
        return self::where('id', $id)->first();
    }

    public static  function updateSurveyQuestionAnswer($data, $id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $id)->update($data);
    }

    public static  function updateLogicCome($question_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('question_id', $question_id)->update(['logic_come' => self::NOT_LOGIC]);
    }

    public static  function deleteSurveyQuestionAnswer($id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('question_id', $id)->orWhere('matrix_question_id', $id)->delete();
    }

    public static  function getListAnswer($id)
    {
        $query = self::select('id', 'question_id', 'matrix_question_id', 'sequence', 'logic_come', 'value', 'value_type')
            ->where('value_type', self::ROW)
            // ->where('deleted', self::NOT_DELETED)
            ->where(function ($query) use ($id) {
                $query->where('question_id', $id)
                    ->orWhere('matrix_question_id', $id);
            });
        return $query->orderBy('sequence', 'asc')->get();
    }
}
