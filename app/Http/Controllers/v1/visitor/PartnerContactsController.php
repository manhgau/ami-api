<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */

namespace App\Http\Controllers\v1\visitor;


use Illuminate\Http\Request;
use Validator;
use App\Helpers\ClientResponse;
use App\Models\PartnerContacts;

class PartnerContactsController extends Controller
{
    public function createPartnerContact(Request $request)
    {

        $validator = Validator::make($request->all(), [
            //required
            'name' => 'required|string|max:250',
            'email_phone' => 'required||string|max:50',
            'year_of_birth' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),

        ]);

        if ($validator->fails()) {
            $errorString = implode(",", $validator->messages()->all());
            return ClientResponse::responseError($errorString);
        }
        try {
            $input = $request->all();
            $input['childrend_age_ranges'] = json_encode($request->childrend_age_ranges, true);
            $data = PartnerContacts::create($input);
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
