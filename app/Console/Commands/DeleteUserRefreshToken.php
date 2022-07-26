<?php

namespace App\Console\Commands;


use App\Models\UserRefreshToken;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use DB;
use Illuminate\Support\Facades\Log;

class DeleteUserRefreshToken extends Command
{
    use DispatchesJobs;

    protected $signature = 'deleteExpire:user-refresh-token';

    protected $description = 'Delete expire user refresh token';

    public function handle(){
        try{
            $start_time = microtime(true);
            $this->line("Start");
            $rs = UserRefreshToken::where('refresh_expire', '<', time())->delete();
            $this->line("Delete result: ".(json_encode($rs)));
            $end_time = microtime(true);

            $this->line("Time: ".($end_time - $start_time));
            $this->line("Done");
        }catch (\Exception $ex){
            Log::error("#ERROR: deleteExpire:user-refresh-token ".$ex->getMessage());
        }
    }
}
