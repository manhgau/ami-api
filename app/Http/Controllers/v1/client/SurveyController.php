<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\Survey;
use App\Models\SurveyUser;
use Validator;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function createSurvey(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'category_id' => 'required|integer|max:20',
            ]);
            if($validator->fails()){
                $errorString = implode(",",$validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input = $request->all();
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            if(Survey:: CheckNumberOfSurvey($user_id) == false){
                return ClientResponse::response(ClientResponse::$survey_user_number, 'Số lượng khảo sát của bạn đã hết, Vui lòng đăng ký gói cước để có thêm lượt tạo khảo sát');
            }
            $input['user_id'] = $user_id;
            $input['created_by'] = $user_id;
            $survey=Survey::create($input);
            if(!$survey){
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Thêm mới thành công', $survey);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getListSurvey( Request $request) {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $ckey  = CommonCached::cache_find_survey_user . "_" . $user_id."_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = Survey::getListSurvey($perPage,  $page, $user_id);
                $datas = RemoveData::removeUnusedData($datas);
                CommonCached::storeData($ckey, $datas);
            }
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getDetailSurvey($id)
    {
        try {
            $ckey  = CommonCached::cache_find_survey_user_by_id . "_" . $id;
            $detail = CommonCached::getData($ckey);
            if (empty($detail)) {
                $detail = Survey::getDetailSurvey($id);
                CommonCached::storeData($ckey, $detail);
            }
            if (!$detail) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function editSurvey(Request $request, $id) {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'category_id' => 'required|integer|max:20',
            ]);
            if($validator->fails()){
                $errorString = implode(",",$validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $survey_user = Survey::getDetailSurvey($id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $data = $request->all();
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $data['user_id'] = $user_id;
            $data['updated_by'] = $user_id;
            $update_survey= Survey::updateSurvey($data, $id);
            if(!$update_survey){
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Update thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function deleteSurvey($id) {
        try {
            $survey_user = Survey::getDetailSurvey($id);
            if (!$survey_user) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            $del_survey = Survey::updateSurvey(['deleted' => Survey::DELETED], $id);
            if(!$del_survey){
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Xóa thành công');
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}


