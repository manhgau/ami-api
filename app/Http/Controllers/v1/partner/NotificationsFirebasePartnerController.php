<?php

/**
 * Created by PhpStorm.
 * User: nguyenpv
 * Date: 06/07/2022
 * Time: 11:15
 */

namespace App\Http\Controllers\v1\partner;


use Illuminate\Http\Request;
use App\Helpers\ClientResponse;
use App\Helpers\Common\CommonCached;
use App\Helpers\Context;
use App\Helpers\RemoveData;
use App\Models\District;
use App\Models\NotificationsFirebasePartners;
use App\Models\NotificationType;

class NotificationsFirebasePartnerController extends Controller
{
    public function getListNotificationPartner(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $perPage = $request->per_page ?? 10;
                    $page = $request->current_page ?? 1;
                    $partner_id = $partner->id ?? 0;
                    $lists = NotificationsFirebasePartners::getListNotificationPartner($perPage,  $page, $partner_id);
                    $lists = RemoveData::removeUnusedData($lists);
                    $count = NotificationsFirebasePartners::countlNotificationPartner();
                    if (!$lists) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    $data = [
                        'count' => $count,
                        'list' => $lists,
                    ];
                    return ClientResponse::responseSuccess('OK', $data);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            } else {
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }
    }

    public function getDetailNotificationPartner(Request $request)
    {
        $tokenInfo = Context::getInstance()->get(Context::PARTNER_ACCESS_TOKEN);
        if ($tokenInfo) {
            $partner = $tokenInfo->partner;
            if ($partner) {
                try {
                    $notification_partner_id = $request->notification_partner_id;
                    $partner_id = $partner->id ?? 0;
                    $detail = NotificationsFirebasePartners::getDetailNotificationPartner($partner_id, $notification_partner_id);
                    if (!$detail) {
                        return ClientResponse::responseError('Không có bản ghi phù hợp');
                    }
                    NotificationsFirebasePartners::updateNotificationPartner(['is_viewed' => NotificationsFirebasePartners::VIEW_ACTIVE], $notification_partner_id);
                    return ClientResponse::responseSuccess('OK', $detail);
                } catch (\Exception $ex) {
                    return ClientResponse::responseError($ex->getMessage());
                }
            } else {
                return ClientResponse::response(ClientResponse::$required_login_code, 'Tài khoản chưa đăng nhập');
            }
        }
    }

    public function getNotficationType(Request $request)
    {

        try {
            $datas = NotificationType::getNotficationType();
            if (!$datas) {
                return ClientResponse::responseError('Không có bản ghi phù hợp');
            }
            return ClientResponse::responseSuccess('OK', $datas);
        } catch (\Exception $ex) {
            return ClientResponse::responseError($ex->getMessage());
        }
    }
}
