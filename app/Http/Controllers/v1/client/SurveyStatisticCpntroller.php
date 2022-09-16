<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\RemoveData;
use App\Models\QuestionType;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class SurveyStatisticCpntroller extends Controller
{
    public function getSurveyStatistic(Request $request)
    {
        $survey_id = $request->survey_id;
        $is_anynomous = $request->is_anynomous ?? null;
        $result = SurveyPartnerInputLine::getSurveyStatistic($survey_id,  $is_anynomous);
        return $result;
    }
    public function getSurveyStatisticDetail(Request $request)
    {
        $perPage = $request->per_page ?? 5;
        $page = $request->current_page ?? 1;
        $is_anynomous = $request->is_anynomous;
        $survey_id = $request->survey_id;
        $question_id = $request->question_id;
        $list = '';
        $chart = '';
        $survey_questions = SurveyQuestion::getDetailSurveyQuestion($question_id);
        $question_type = $survey_questions->question_type;
        switch ($question_type) {
            case QuestionType::MULTI_CHOICE_CHECKBOX:
            case QuestionType::MULTI_CHOICE_RADIO:
            case QuestionType::MULTI_CHOICE_DROPDOWN:
                $data =  SurveyPartnerInputLine::getSurveyStatisticCheckbox($question_id, $survey_id, $is_anynomous);
                $chart = $data;
                break;
            case QuestionType::RATING_STAR:
                $data =  SurveyPartnerInputLine::getSurveyStatisticRating($question_id, $survey_id, $is_anynomous);
                $chart = $data;
                break;
            case QuestionType::DATETIME_DATE:
            case QuestionType::DATETIME_DATE_RANGE:
            case QuestionType::QUESTION_ENDED_SHORT_TEXT:
            case QuestionType::QUESTION_ENDED_LONG_TEXT:
                $data =  SurveyPartnerInputLine::getSurveyStatisticTextOrDate($perPage, $page,  $question_id, $survey_id, $question_type, $is_anynomous);
                $list = $data;
                break;
            case QuestionType::MULTI_FACTOR_MATRIX:
                $data =  SurveyPartnerInputLine::getSurveyStatisticMatrix($question_id, $survey_id, $is_anynomous);
                $chart = $data;
                break;
            default:
                return ClientResponse::responseError('question type không hợp lệ', $question_type);
                break;
        }
        $result = [
            'question_name' => $survey_questions->title,
            'sequence' => $survey_questions->sequence,
            'question_type' => $question_type,
            'chart' => $chart,
            'list_values' => $list,
        ];
        return ClientResponse::responseSuccess('OK', $result);
    }
}
