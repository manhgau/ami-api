<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 28/07/2022
 * Time: 13:58
 */
namespace App\Http\Controllers\v1\common;

use App\Helpers\ClientResponse;
use Cache;
use Illuminate\Support\Facades\Artisan;

class ToolsController extends Controller
{

    public function clearConfigCache(){
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        return ClientResponse::responseSuccess('Xóa cache config thành công');
    }


    public function deleteCache(){
        Cache::flush();
        return ClientResponse::responseSuccess('Xóa cache thành công');
    }

}