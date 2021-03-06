<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class ReloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 任务最大尝试次数
     *
     * @var int
     */
    public $tries = 3;


    /**
     * 执行任务的最长时间
     */

    public $timeout=15;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // reload server
//        $base_project_path = config('base_project_path');
//        shell_exec("/usr/local/openresty/nginx/sbin -c $base_project_path/storage/app/server/nginx/nginx.conf -s reload");
        // command
        $exitCode = Artisan::call('WebServer', ['cmd' => 'reload']);
//        Artisan::call('TestCommand', []);
    }
}
