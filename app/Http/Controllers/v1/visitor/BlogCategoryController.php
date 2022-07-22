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
use App\Helpers\Common\ConstValue;
use App\Models\BlogCategory;

class BlogCategoryController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $perPage = $request->per_page??10;
            $page = $request->page??1;
            $datas = BlogCategory::getAll($perPage,  $page);
            $datas = $this->__removeUnusedData($datas);
            if (!$datas) {
                return ClientResponse::responseError('Blog Category not found');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    private function __removeUnusedData($datas){        
        $unuse_data_arr = ['first_page_url', 'from' ,'to','links','last_page','last_page_url','next_page_url','prev_page_url','path'];     
        if(is_array($datas) && count($datas) > 0){
            foreach($unuse_data_arr as $key){
                if(isset($datas[$key])){
                    unset($datas[$key]);
                }
            }
        }
        return $datas;   
    }


    public function getDetail($id)
    {
        try {
            $detail = BlogCategory::getDetail($id);
            if (!$detail) {
                return ClientResponse::responseError('Blog Category not found');
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
