<?php

namespace App\Helpers;

use App\Models\SurveyTemplate;
use Exception;

class FtpSv
{
    const LOGO_FOLDER                          = 'uploads/user/logo';
    const AVATAR_FOLDER                        = 'uploads/user/avatar';
    const BACKGROUND_SURVEY_FOLDER             = 'uploads/survey/background';
    const BACKGROUND_QUESTION_FOLDER           = 'uploads/question/background';
    public static function upload($file, $file_name, $ftpPath, $template_id, $temp_path)
    {
        try {
            $folder = dirname(dirname(dirname(__FILE__))) . '/public/' . $temp_path;
            $file->move($folder, $file_name);
            $localPath = $folder . '/' . $file_name;
            $ftp_server = env('FTP_HOST');
            $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to server");
            $login = @ftp_login($ftp_conn, env('FTP_USERNAME'), env('FTP_PASSWORD'));
            @ftp_chdir($ftp_conn,  $ftpPath);
            $today = date('Y/m/d', time());
            foreach (explode('/', $today) as $fName) {
                $temp_path .= '/' . $fName;
                $ftpPath .= '/' . $fName;
                if (!@ftp_chdir($ftp_conn, $fName)) {
                    ftp_mkdir($ftp_conn, $fName);
                    ftp_chdir($ftp_conn, $fName);
                } else {
                    ftp_chmod($ftp_conn, 0755, $ftpPath);
                }
            }
            $ftp_put = $ftpPath . '/' . $file_name;
            if (ftp_put($ftp_conn, $ftp_put, $localPath, FTP_BINARY)) {
                unlink($localPath);
                ftp_chmod($ftp_conn, 0755, $ftpPath . '/' . $file_name);
                ftp_close($ftp_conn);
                $data =  '/' . $temp_path . '/' . $file_name;
                return  $data;
            }
            unlink($localPath);
            ftp_close($ftp_conn);
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
