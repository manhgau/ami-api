<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Models\SurveyQuestionAnswer;
use Illuminate\Http\Request;

class SurveyQuestionAnswersController extends Controller
{
    public function getListAnswers(Request $request)
    {
        try {
            $question_id = $request->question_id;
            $data = SurveyQuestionAnswer::getAllSurveyQuestionAnswer($question_id)
                ->orWhere('matrix_question_id', $question_id)->get();
            if (!$data) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
