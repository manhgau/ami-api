<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckProjectIsAlsmostExpires extends Command
{

    protected $signature = 'check-project-is-almost-expires';

    protected $description = '...';

    public function handle()
    {
        try {
            $start_time = microtime(true);
            $this->line("Start");
            $do_something = false;
            $do_something = \App\Helpers\CheckProjectIsAlsmostExpires::checkProjectIsAlmostExpires();
            if ($do_something == true) {
                $this->line('Action complete');
            } else {
                $this->line('Nothing to do');
            }
            $end_time = microtime(true);
            $this->line("Time: " . ($end_time - $start_time));
            $this->line("Done");
        } catch (\Exception $ex) {
            Log::error("#ERROR: update-state-survey " . $ex->getMessage());
        }
    }
}
