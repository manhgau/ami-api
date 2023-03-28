<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SurveyPartner extends Model
{
    protected $fillable = ['is_save'];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const SAVE = 1;
    const NO_SAVE = 0;
    const NOT_DELETED  = 0;
    const DELETED  = 1;
    const CLOSED  = 'closed';
    const COMPLETED  = 'completed';
    const NOT_COMPLETED  = 'not_completed';
    const ON_PROGRESS  = 'on_progress';

    public static  function getlistSurveyPartner($perPage = 10,  $page = 1, $partner_id, $time_now, $time_end, $is_save = null, $search = null, $status = null)
    {
        $query = DB::table('survey_partners as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->select(
                'b.title',
                'a.id as survey_partner_id',
                'b.id as survey_id',
                'b.point',
                'b.state_ami',
                'a.is_save',
                'a.number_of_response_partner',
                'b.limmit_of_response',
                'b.number_of_response',
                'b.start_time',
                'b.end_time',
                'b.question_count as count_questions',
                'b.view',
                'b.attempts_limit_min',
                'b.attempts_limit_max',
                'b.is_answer_single',
            )
            ->where('a.stattus', self::STATUS_ACTIVE)
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.partner_id', $partner_id)
            ->where('b.start_time', '<', $time_now)
            ->where('b.end_time', '>', $time_end)
            ->orderBy('b.created_at', 'desc');
        if ($is_save != null) {
            $query->where('a.is_save', $is_save);
        } else {
            $query->where('a.is_save', self::NO_SAVE);
        }
        if ($search != null) {
            $query->where('b.title', 'like', '%' . $search . '%');
        }
        if ($status == self::CLOSED) {
            $query->where('b.end_time', '<', Carbon::now());
        }
        if ($status == self::ON_PROGRESS) {
            $query->where('b.end_time', '>', Carbon::now())->where('b.state_ami', Survey::STATUS_ON_PROGRESS);
        }
        return $query->paginate($perPage, "*", "page", $page)->toArray();
    }

    public static  function updateSurveyPartner($data, $survey_partner_id)
    {
        return self::where('deleted', self::NOT_DELETED)->where('id', $survey_partner_id)->update($data);
    }

    public static  function checkSurveyPartner($survey_id, $partner_id)
    {
        return self::where('deleted', self::NOT_DELETED)
            ->where('survey_id', $survey_id)
            ->where('partner_id', $partner_id)
            ->first();
    }

    public static  function getDetailSurveyPartner($survey_partner_id)
    {
        return DB::table('survey_partners as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->select(
                'a.id as survey_partner_id',
                'b.title',
                'b.description',
                'b.id as survey_id',
                'b.state_ami',
                'a.is_save',
                'b.point',
                'b.start_time',
                'b.end_time',
                'b.question_count as count_questions',
                'b.view',
                'b.attempts_limit_min',
                'b.attempts_limit_max',
                'b.is_answer_single',
            )
            ->where('a.deleted', self::NOT_DELETED)
            ->where('a.id', $survey_partner_id)
            ->first();
    }



    public static  function getAllSurveyIsAlmostExpires($time)
    {
        $query = DB::table('survey_partners as a')
            ->join('surveys as b', 'b.id', '=', 'a.survey_id')
            ->select(
                'b.title',
                'a.id as survey_partner_id',
                'b.id as survey_id',
                'a.is_save',
                'a.partner_id',
                'b.number_of_response',
                'b.start_time',
                'b.end_time',
                'b.attempts_limit_min',
                'b.attempts_limit_max',
                'b.is_answer_single',
            )
            ->where('a.stattus', self::STATUS_INACTIVE)
            ->whereDate('b.end_time', $time)
            ->where('a.deleted', self::NOT_DELETED);
        return $query->get()->toArray();
    }
}
