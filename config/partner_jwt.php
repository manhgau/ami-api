<?php

/**
 * Created by NguyenPV.
 * User: nguyenpv
 * Date: 20/07/2022
 * Time: 14:09
 */
return [
    'ttl' => env('PARTNER_JWT_TTL', 1),    //1 phút
    'refresh_ttl' => env('PARTNER_JWT_REFRESH_TTL', 15), //15 phút
];
