<?php
namespace App\Helpers;
class removeData
{
    public static function removeUnusedData($datas){        
        $unuse_data_arr = ['first_page_url', 'from' ,'to','links','last_page','last_page_url','next_page_url','prev_page_url','path'];     
        if(is_array($datas) && count($datas) > 0){
            foreach($unuse_data_arr as $key){
                if(isset($datas[$key])){
                    unset($datas[$key]);
                }
            }
        }
        return $datas;   
    }
}