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
use App\Models\AcademicLevel;
use App\Models\JobType;

class AcademicLevelCotroller extends Controller
{
    public function getAcademicLevel(Request $request)
    {

        try {
            $perPage = $request->per_page??100;
            $page = $request->page??1;
            $datas = AcademicLevel::getAcademicLevel($perPage,  $page);
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
