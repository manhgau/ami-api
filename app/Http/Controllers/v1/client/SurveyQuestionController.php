<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Models\Package;
use App\Models\QuestionType;
use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionAnswer;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyQuestionController extends Controller
{
    public function createSurveyQuestion(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'question_type' => 'required|string',
            ]);
            if($validator->fails()){
                $errorString = implode(",",$validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input = $request->all();
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if((SurveyQuestion:: countQuestion($user_id)) >= (Package::checkTheUserPackage($user_id)->limit_questions)){
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng câu hỏi khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm câu hỏi khảo sát');
            }
            $input['user_id'] = $user_id;
            $input['created_by'] = $user_id;
            $input['survey_id'] = $request->survey_id;
            $question_type = Str::lower($request->question_type);
            
            if(QuestionType::checkQuestionTypeValid($question_type) === false) {
                return ClientResponse::responseError('Lỗi ! Không có dạng câu hỏi khảo sát này.');
            }
            switch ($question_type)
                {
                    case QuestionType::MULTI_CHOICE_CHECKBOX:
                    case QuestionType::MULTI_CHOICE_RADIO:
                    case QuestionType::MULTI_CHOICE_DROPDOWN:
                    case QuestionType::RATING_STAR: 
                        $servey_question = SurveyQuestion::createSurveyQuestion($input);
                        if(!$servey_question){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $data = [];
                        foreach ($request->responde as $key => $value){
                                $data_insert = $value;
                                $data_insert['question_id'] = $servey_question->id;
                                $data[$key] = $data_insert;
                        }
                        $survey_question_answer = SurveyQuestionAnswer::insert($data);
                        if(!$survey_question_answer){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        return ClientResponse::responseSuccess('Thêm mới thành công', $servey_question);
                        break;
                    case QuestionType::DATETIME_DATE:
                    case QuestionType::DATETIME_DATE_RANGE:
                    case QuestionType::QUESTION_ENDED_SHORT_TEXT: 
                    case QuestionType::QUESTION_ENDED_LONG_TEXT: 
                        $servey_question = SurveyQuestion::createSurveyQuestion($input);
                        if(!$servey_question){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        return ClientResponse::responseSuccess('Thêm mới thành công', $servey_question);

                        break;
                    case QuestionType::MULTI_FACTOR_MATRIX: 
                        $servey_question = SurveyQuestion::createSurveyQuestion($input);
                        if(!$servey_question){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $data = [];
                        foreach ($request->responde as $key => $value){
                                $data_insert = $value;
                                if($data_insert['value_type'] == QuestionType::MATRIX_VALUE_COLUMN){
                                    $data_insert['matrix_question_id'] = $servey_question->id;
                                    $data_insert['question_id'] = 0;
                                }else{
                                    $data_insert['question_id'] = $servey_question->id;
                                    $data_insert['matrix_question_id'] = 0;
                                }
                                $data[$key] = $data_insert;
                        }
                        $survey_question_answer = SurveyQuestionAnswer::insert($data);
                        if(!$survey_question_answer){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        return ClientResponse::responseSuccess('Thêm mới thành công', $servey_question);
                        break;
                    default:
                        return ClientResponse::responseError('question type không hợp lệ', $question_type);
                        break;
                }
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getListSurveyQuestion(Request $request) {
        try {
            $survey_id = $request->survey_id;
            $ckey  = CommonCached::cache_find_survey_question_by_survey_id. "_" . $survey_id;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $list = SurveyQuestion::getListSurveyQuestion($survey_id);
                if (!$list) {
                    return ClientResponse::responseError('Không có bản ghi phù hợp');
                }
                $datas = [];
                foreach ($list as $key => $value) {
                        switch ($value['question_type']) { // question_id 
                            case QuestionType::MULTI_FACTOR_MATRIX: 
                                $data_response = $value;
                                $data_response['response'] =SurveyQuestionAnswer::getAllSurveyQuestionAnswer($value['id'])->orWhere('matrix_question_id', $value['id'])->get();
                                $datas[$key]=$data_response;
                                break;
                            case QuestionType::MULTI_CHOICE_CHECKBOX: 
                            case QuestionType::MULTI_CHOICE_RADIO:  
                            case QuestionType::MULTI_CHOICE_DROPDOWN: 
                            case QuestionType::RATING_STAR: 
                                $data_response = $value;
                                $data_response['response'] =SurveyQuestionAnswer::getAllSurveyQuestionAnswer($value['id'])->get();
                                $datas[$key]=$data_response;
                                break; 
                            case QuestionType::DATETIME_DATE: 
                            case QuestionType::DATETIME_DATE_RANGE: 
                            case QuestionType::QUESTION_ENDED_SHORT_TEXT: 
                            case QuestionType::QUESTION_ENDED_LONG_TEXT: 
                                $data_response = $value;
                                $datas[$key]=$data_response;
                                break; 
                            default:
                                return ClientResponse::responseError('question type không hợp lệ', $value['question_type']);
                                break;       
                        }
                }
                CommonCached::storeData($ckey, $datas, true);
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
 
    public static function updateSurveyQuestion( Request $request ) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
            ]);
            if($validator->fails()){
                $errorString = implode(",",$validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_user = SurveyQuestion::getDetailSurveyQuestion($request->question_id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $input = $request->all();
            $input['updated_by'] = $user_id;
            $question_type = Str::lower($request->question_type);
            if($question_type && $question_type !== $survey_user->question_type){
                if(QuestionType::checkQuestionTypeValid($question_type) === false) {
                    return ClientResponse::responseError('Lỗi ! Không có dạng câu hỏi khảo sát này.');
                }
                $survey_user->delete();
                SurveyQuestionAnswer::deleteSurveyQuestionAnswer($survey_user->id);
                $input['user_id'] = $user_id;
                $input['created_by'] = $user_id;
                $input['survey_id'] = $request->survey_id;
                $input['title'] = $request->title??$survey_user->title;
                $input['sequence'] = $request->sequence??$survey_user->sequence;
                switch ($question_type)
                {
                    case QuestionType::MULTI_CHOICE_CHECKBOX:
                    case QuestionType::MULTI_CHOICE_RADIO:
                    case QuestionType::MULTI_CHOICE_DROPDOWN:
                    case QuestionType::RATING_STAR: 
                        $servey_question = SurveyQuestion::createSurveyQuestion($input);
                        if(!$servey_question){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $data = [];
                        foreach ($request->responde as $key => $value){
                                $data_insert = $value;
                                $data_insert['question_id'] = $servey_question->id;
                                $data[$key] = $data_insert;
                        }
                        $survey_question_answer = SurveyQuestionAnswer::insert($data);
                        if(!$survey_question_answer){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        return ClientResponse::responseSuccess('Thêm mới thành công', $servey_question);
                        break;
                    case QuestionType::DATETIME_DATE:
                    case QuestionType::DATETIME_DATE_RANGE:
                    case QuestionType::QUESTION_ENDED_SHORT_TEXT: 
                    case QuestionType::QUESTION_ENDED_LONG_TEXT: 
                        $servey_question = SurveyQuestion::createSurveyQuestion($input);
                        if(!$servey_question){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        return ClientResponse::responseSuccess('Thêm mới thành công', $servey_question);

                        break;
                    case QuestionType::MULTI_FACTOR_MATRIX: 
                        $servey_question = SurveyQuestion::createSurveyQuestion($input);
                        if(!$servey_question){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        $data = [];
                        foreach ($request->responde as $key => $value){
                                $data_insert = $value;
                                if($data_insert['value_type'] == QuestionType::MATRIX_VALUE_COLUMN){
                                    $data_insert['matrix_question_id'] = $servey_question->id;
                                    $data_insert['question_id'] = 0;
                                }else{
                                    $data_insert['question_id'] = $servey_question->id;
                                    $data_insert['matrix_question_id'] = 0;
                                }
                                $data[$key] = $data_insert;
                        }
                        $survey_question_answer = SurveyQuestionAnswer::insert($data);
                        if(!$survey_question_answer){
                            return ClientResponse::responseError('Đã có lỗi xảy ra');
                        }
                        return ClientResponse::responseSuccess('Cập nhập thành công', $servey_question);
                        break;
                    default:
                        return ClientResponse::responseError('question type không hợp lệ', $question_type);
                        break;
                }


            }
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $input['updated_by'] = $user_id;
            $update_survey= SurveyQuestion::updateSurveyQuestion($input, $request->question_id);
            if(!$update_survey){
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public static function updateSurveyQuestionAnswer( Request $request ) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
            ]);
            if($validator->fails()){
                $errorString = implode(",",$validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_user = SurveyQuestionAnswer::getDetailSurveyQuestionAnswer($request->answer_id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $data = $request->all();
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $data['updated_by'] = $user_id;
            $update_survey= SurveyQuestionAnswer::updateSurvey($data, $request->answer_id);
            if(!$update_survey){
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }

    }

    public static function delSurveyQuestion(Request $request) {
        try {
            $survey_user = SurveyQuestion::getDetailSurveyQuestion($request->question_id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $del_survey = SurveyQuestion::updateSurveyQuestion(['deleted' => SurveyQuestion::DELETED], $request->question_id);
            if(!$del_survey){
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Xóa thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }

    }

    
}


