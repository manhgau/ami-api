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

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function testEmail()
    {
        $web_link = env('FRONTEND_APP_URL');
        $mailData = [
            'to'    =>  'phamvannguyen.haui@gmail.com',
            'active_link' => ''.$web_link.'/client/active-account?uid=10&activeCode=56287_6290f20d34fd3',
            'subject'   =>  'Kích hoạt tài khoản AMI Survey của bạn',
            'expire_time'   =>  date('H:i:s d/m/Y' ,(time() + 86400)),
        ];
        // send now
        //Mail::to($mailData['to'])->send(new ActiveAccount($mailData));
        //jobs
        dispatch(new SendActiveAcountEmailJob($mailData));

        dd("Email is sent successfully.");
    }
}