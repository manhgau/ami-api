<?php

namespace App\Helpers;

class RemoveData
{
    public static function removeUnusedData($datas)
    {
        $unuse_data_arr = ['first_page_url', 'from', 'to', 'links', 'last_page', 'last_page_url', 'next_page_url', 'prev_page_url', 'path'];
        if (is_array($datas) && count($datas) > 0) {
            foreach ($unuse_data_arr as $key) {
                if (isset($datas[$key]) ||  $datas[$key] == null) {
                    unset($datas[$key]);
                }
            }
        }
        return $datas;
    }

    public static function removeUnusedDataCopySurvey($datas)
    {
        $unuse_data_arr = ['state_ami', 'limmit_of_response_anomyous', 'created_at', 'updated_at', 'background', 'view', 'number_of_response', 'limmit_of_response', 'real_end_time', 'end_time', 'point', 'is_attempts_limited', 'attempts_limit_max', 'attempts_limit_min', 'is_answer_single'];
        if (is_array($datas) && count($datas) > 0) {
            foreach ($unuse_data_arr as $key) {
                if (isset($datas[$key]) ||  $datas[$key] == null) {
                    unset($datas[$key]);
                }
            }
        }
        return $datas;
    }
}
