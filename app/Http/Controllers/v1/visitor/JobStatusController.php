<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */

namespace App\Http\Controllers\v1\visitor;


use Illuminate\Http\Request;
use App\Helpers\ClientResponse;
use App\Helpers\RemoveData;
use App\Models\JobStatus;
use App\Models\Page;

class JobStatusController extends Controller
{
    public function getJobStatus(Request $request)
    {

        try {
            $perPage = $request->per_page??100;
            $page = $request->page??1;
            $datas = JobStatus::getJobStatus($perPage,  $page);
            $datas = RemoveData::removeUnusedData($datas);
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
