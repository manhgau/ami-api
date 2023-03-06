<?php

/**
 * Created by NguyenPV.
 * User: nguyenpv
 * Date: 20/07/2022
 * Time: 14:09
 */
return [
    'ttl' => env('PARTNER_JWT_TTL', 60),    //60 phút
    'refresh_ttl' => env('PARTNER_JWT_REFRESH_TTL', 129600), //3 tháng tính theo số phút
];
