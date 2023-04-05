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
use App\Helpers\Context;
use App\Models\Contact;
use Validator;


class ContactController extends Controller
{
    public function addContact(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fullname' => 'required|string|max:255',
                'email' => 'required|string|max:255',
                'phone' => 'max:50',
                'company_name' => 'max:255',
                'message' => 'max:500',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            $input = $request->all();
            $input['status'] = Contact::PENDING;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $user_id ? $input['user_id'] = $user_id : $input['user_id'] = null;
            $result = Contact::create($input);
            if (!$result) {
                return ClientResponse::responseError('Đã có lỗi xảy ra');
            }
            return ClientResponse::responseSuccess('Thêm mới thành công', $result);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
