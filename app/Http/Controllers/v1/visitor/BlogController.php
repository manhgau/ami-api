<?php

namespace App\Http\Controllers\v1\visitor;


use App\Helpers\ClientResponse;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function getAll(Request $request)
    {
        try {
            $perPage = $request->per_page??10;
            $page = $request->page??1;
            $category_id = $request->category_id;
            $data = Blog::getAll( $perPage, $page,  $category_id);
            $data = $this->__removeUnusedData($data);
            if (!$data) {
                return ClientResponse::responseError('Blog not found');
            }
            return ClientResponse::responseSuccess('OK', $data);
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

    public function getDetail($slug)
    {
        try {
            $detail = Blog::getDetail($slug);
            if(!$detail){
                return ClientResponse::responseError('Blog not found');
            }
            return ClientResponse::responseSuccess('OK', $detail);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

}
