<?php

namespace App\Http\Controllers\v1\client;

use App\Helpers\ClientResponse;
use App\Models\Sms;
use Illuminate\Http\Request;


class SmsController extends Controller
{
    public function sendSms(Request $request)
    {
        $phone = $request->phone;
        $message = $request->message;
        $result = Sms::sendSms($phone, $message);
        if (!$result) {
            return ClientResponse::responseError('Đã có lỗi xảy ra');
        }
        return ClientResponse::responseSuccess('OK', $result);
    }
}
