<?php

namespace App\Http\Controllers\v1\client;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Context;
use App\Models\QuestionType;
use App\Models\SurveyPartnerInputLine;
use App\Models\SurveyQuestion;

class SurveyPartnerInputLineAnynomousController extends Controller
{

    public function surveyPartnerInputLineAnynomous(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $question_id = $request->question_id??0;
            $input['question_id']   = $question_id;
            $input['survey_id']     = $request->survey_id;
            $input['partner_input_id']   = $request->partner_input_id;
            $survey_question = SurveyQuestion::getDetailSurveyQuestion($question_id);
            $input['question_sequence']     = $survey_question->sequence;
            $input['answer_type']   = $survey_question->question_type;
            $input['answer_score']   = $request->answer_score??0;
            switch ($input['answer_type'])
            {
                case QuestionType::MULTI_CHOICE_CHECKBOX:
                case QuestionType::MULTI_CHOICE_RADIO:
                case QuestionType::MULTI_CHOICE_DROPDOWN:
                    $target_ids = $request->suggested_answer_id;
                    if (is_array($target_ids)){
                        $data_input = [];
                        foreach ($target_ids  as $key => $value){
                            $input['suggested_answer_id'] = $value;
                            $data_input[$key] = $input;
                        }
                    $result=SurveyPartnerInputLine::insert($data_input);
                    if(!$result){
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);
                    }
                    break;
                case QuestionType::RATING_STAR: 
                    $target_ids = $request->suggested_answer_id;
                    $input['value_star_rating']= $request->value_star_rating??'';
                    if (is_array($target_ids)){
                        $data_input = [];
                        foreach ($target_ids  as $key => $value){
                            $input['suggested_answer_id'] = $value;
                            $data_input[$key] = $input;
                        }
                    $result=SurveyPartnerInputLine::insert($data_input);
                    if(!$result){
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);
                    }
                    break;
                case QuestionType::DATETIME_DATE_RANGE:
                    $input['value_date_start'] = $request->value_date_start??'';
                    $input['value_date_end'] = $request->value_date_end??'';
                    $result=SurveyPartnerInputLine::create($input);
                    if(!$result){
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);
                    break;
                case QuestionType::DATETIME_DATE:
                    $input['value_date'] = $request->value_date??'';
                    $result=SurveyPartnerInputLine::create($input);
                    if(!$result){
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);
                    break;
                case QuestionType::QUESTION_ENDED_SHORT_TEXT: 
                case QuestionType::QUESTION_ENDED_LONG_TEXT: 
                    $input['value_text_box'] = $request->value_text_box??'';
                    $result=SurveyPartnerInputLine::create($input);
                    if(!$result){
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);
                    break;
                case QuestionType::MULTI_FACTOR_MATRIX: 
                    $data = $request->all();
                    if (is_array($data)){
                        $data_input = [];
                        foreach ($data  as $key => $value){
                            $input['matrix_row_id'] = $value['matrix_row_id'];
                            $input['matrix_column_id'] = $value['matrix_column_id'];
                            $data_input[$key] = $input;
                        }
                    $result=SurveyPartnerInputLine::insert($data_input);
                    if(!$result){
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Thêm mới thành công', $result);
                    }
                    break;
                default:
                    return ClientResponse::responseError('question type không hợp lệ', $input['answer_type']);
                    break; 

            }
        }catch (\Exception $ex){
            return ClientResponse::responseError($ex->getMessage());
        }
       
    }
}
