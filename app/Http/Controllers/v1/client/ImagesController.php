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
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Helpers\FtpSv;
use App\Helpers\RemoveData;
use App\Models\Images;
use Validator;


class ImagesController extends Controller
{
    public function uploadImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'background' => 'required|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);
            if ($validator->fails()) {
                $errorString = implode(",", $validator->messages()->all());
                return ClientResponse::responseError($errorString);
            }
            if ($file = $request->file('background')) {
                $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
                $size = filesize($file);
                $input['type'] = $file->extension();
                $input['user_id'] = $user_id;
                $input['size'] = $size;
                $input['upload_ip'] = $request->ip();
                $name =   md5($file->getClientOriginalName() . rand(1, 9999)) . '.' . $file->extension();
                $path = env('FTP_PATH') . FtpSv::BACKGROUND_QUESTION_FOLDER;
                $image = FtpSv::upload($file, $name, $path, FtpSv::BACKGROUND_QUESTION_FOLDER);
                $input['image'] = $image;
                $insert = Images::create($input);
                if (!$insert) {
                    return ClientResponse::responseError('Đã có lỗi xảy ra');
                }
                return ClientResponse::responseSuccess('Thêm mới thành công', $insert);
            }
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getTemplateImage(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 100;
            $page = $request->current_page ?? 1;
            $ckey  = CommonCached::api_template_image . "_" . $perPage . "_" . $page;
            $datas = CommonCached::getData($ckey);
            if (empty($datas)) {
                $datas = Images::getTemplateImage($perPage,  $page);
                $datas = RemoveData::removeUnusedData($datas);
                CommonCached::storeData($ckey, $datas);
            }
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
