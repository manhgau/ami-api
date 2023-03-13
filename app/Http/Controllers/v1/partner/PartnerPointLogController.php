<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */

namespace App\Http\Controllers\v1\partner;


use Illuminate\Http\Request;
use App\Helpers\ClientResponse;
use App\Helpers\FormatDate;
use App\Helpers\RemoveData;
use App\Models\PartnerPointLog;

class PartnerPointLogController extends Controller
{
    public function getListHistoryPointLog(Request $request)
    {

        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $status = $request->status;
            $datas =  PartnerPointLog::getListHistoryPointLog($perPage,  $page, $status);
            $datas = RemoveData::removeUnusedData($datas);
            foreach ($datas['data'] as $key => $value) {
                $value->created_at = FormatDate::formatDateStatisticNoTime($value->created_at);
                $value->updated_at = FormatDate::formatDateStatisticNoTime($value->updated_at);
                $datas[$key] = $value;
            }
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
