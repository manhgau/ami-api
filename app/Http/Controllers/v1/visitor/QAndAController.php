<?php

namespace App\Http\Controllers\v1\visitor;


use App\Helpers\ClientResponse;
use App\Helpers\RemoveData;
use App\Models\Blog;
use App\Models\QAndA;
use Illuminate\Http\Request;

class QAndAController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $perPage = $request->per_page??10;
            $page = $request->page??1;
            $category_id = $request->category_id;
            $data = QAndA::getAll( $perPage, $page,  $category_id);
            $data = RemoveData::removeUnusedData($data);
            if (!$data) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getQAndARelate(Request $request)
    {
        try {
            $perPage = $request->per_page??10;
            $page = $request->page??1;
            $slug = $request->slug;
            $detail = QAndA::getDetail($slug);
            if(!$detail){
                return ClientResponse::responseSuccess('Không có bản ghi liên quan');
            }
            $category_id = $detail->category_id;
            $data = QAndA::getQAndARelate( $perPage, $page,  $category_id, $slug);
            $data = RemoveData::removeUnusedData($data);
            if (!$data) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getDetail($slug)
    {
        try {
            $detail = QAndA::getDetail($slug);
            if(!$detail){
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

}
