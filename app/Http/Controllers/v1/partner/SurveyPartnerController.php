<?php

namespace App\Http\Controllers\v1\partner;

use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\Survey;
use App\Models\SurveyPartner;
use Carbon\Carbon;

class SurveyPartnerController extends Controller
{

    public function getlistSurveyPartner(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $partner_id = $partner->id ?? 0;
                    $perPage = $request->per_page ?? 5;
                    $page = $request->current_page ?? 1;
                    $search = $request->search;
                    $is_save = (int)$request->is_save ?? null;
                    $time_now = Carbon::now();
                    $time_end = date('Y-m-d H:i:s', time() - (30 * 86400));
                    $datas = SurveyPartner::getlistSurveyPartner($perPage,  $page, $partner_id, $time_now,  $time_end, $is_save, $search);
                    $datas = RemoveData::removeUnusedData($datas);
                    $array = array();
                    foreach ($datas['data'] as $key => $value) {
                        $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $value->end_time)->timestamp;
                        $time_remaining = $timestamp - Carbon::now()->timestamp;
                        $data = json_decode(json_encode($value), true);
                        $data['time_remaining'] = max(0, $time_remaining);
                        $array[$key] = $data;
                    }
                    $datas['data'] = $array;
                    if (!$datas) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    return ClientResponse::responseSuccess('OK', $datas);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function getDetailSurveyPartner(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $survey_partner_id = $request->survey_partner_id;
                    $result = SurveyPartner::getDetailSurveyPartner($survey_partner_id);
                    if (!$result) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    Survey::updateSurvey(['view' => $result->view + 1], $result->survey_id);
                    $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $result->end_time)->timestamp;
                    $time_remaining = $timestamp - Carbon::now()->timestamp;
                    $result = json_decode(json_encode($result), true);
                    $result['time_remaining'] = $time_remaining;
                    return ClientResponse::responseSuccess('OK', $result);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }

    public function saveSurveyPartner(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $survey_partner_id = $request->survey_partner_id;
                    $detail = SurveyPartner::getDetailSurveyPartner($survey_partner_id);
                    if (!$detail) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    $detail->is_save ? $data['is_save'] = SurveyPartner::NO_SAVE : $data['is_save'] = SurveyPartner::SAVE;
                    $result = SurveyPartner::updateSurveyPartner($data, $survey_partner_id);
                    if (!$result) {
                        return ClientResponse::responseError('Đã có lỗi xảy ra');
                    }
                    return ClientResponse::responseSuccess('Cập nhập thành công');
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            }
        } else {
            return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
        }
    }
}
