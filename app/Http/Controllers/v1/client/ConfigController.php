<?php
/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */
namespace App\Http\Controllers\v1\client;

use Illuminate\Http\Request;
use App\Helpers\ClientResponse;
use App\Helpers\Common\ConstValue;
use Mail;
use App\Mail\ActiveAccount;
use App\Jobs\SendActiveAcountEmailJob;

class ConfigController extends Controller
{
    public function settings(Request $request){
        $msg = 'Basic setting for web client';
        $settings = new \stdClass();
        //
        return ClientResponse::responseSuccess($msg, $settings);
    }

}