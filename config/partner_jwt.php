<?php
/**
 * Created by NguyenPV.
 * User: nguyenpv
 * Date: 20/07/2022
 * Time: 14:09
 */
return [
    'ttl' => env('PARTNER_JWT_TTL', 1500),    //15 phút
    'refresh_ttl' => env('PARTNER_JWT_REFRESH_TTL', 43200), //30 ngày tính theo số phút
];