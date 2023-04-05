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
use App\Helpers\RemoveData;
use App\Models\NotificationsFirebaseClients;

class NotificationsFirebaseClientController extends Controller
{
    public function getListNotificationClient(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 10;
            $page = $request->current_page ?? 1;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $lists = NotificationsFirebaseClients::getListNotificationClient($perPage,  $page, $user_id);
            $lists = RemoveData::removeUnusedData($lists);
            foreach ($lists['data'] as $key => $value) {
                $value->created_at ? $value->created_at = date_format(date_create($value->created_at), 'd/m/Y') : null;
                $value->updated_at ? $value->updated_at = date_format(date_create($value->updated_at), 'd/m/Y') : null;
                $value->description = substr($value->description, 0, 200) . '...';
                $lists['data'][$key] = $value;
            }
            $count = NotificationsFirebaseClients::countlNotificationClient($user_id);
            if (!$lists) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            // $data = [
            //     'count' => $count,
            //     'list' => $lists,
            // ];
            $lists['count'] = $count;
            return ClientResponse::responseSuccess('OK', $lists);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }

    public function getDetailNotificationClient(Request $request)
    {
        try {
            $notification_id = $request->notification_id;
            $user_id = Context::getInstance()->get(Context::CLIENT_USER_ID);
            $detail = NotificationsFirebaseClients::getDetailNotificationClient($user_id, $notification_id);
            $detail->created_at ? $detail->created_at = date_format(date_create($detail->created_at), 'd/m/Y') : null;
            $detail->updated_at ? $detail->updated_at = date_format(date_create($detail->updated_at), 'd/m/Y') : null;
            if (!$detail) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            NotificationsFirebaseClients::updateNotificationClient(['is_viewed' => NotificationsFirebaseClients::VIEW_ACTIVE], $notification_id);
            $count = NotificationsFirebaseClients::countlNotificationClient($user_id);
            $data = [
                'count' => $count,
                'list' => $detail,
            ];
            return ClientResponse::responseSuccess('OK', $data);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
