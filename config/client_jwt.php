<?php
/**
 * Created by NguyenPV.
 * User: nguyenpv
 * Date: 20/07/2022
 * Time: 14:09
 */
return [
    'ttl' => env('CLIENT_JWT_TTL', 1),    //15 phút
    'refresh_ttl' => env('CLIENT_JWT_REFRESH_TTL', 5), //30 ngày, tính theo số phút
];