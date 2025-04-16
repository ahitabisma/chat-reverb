<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StartReverbServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start-reverb-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the Laravel Reverb server process';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Path to PHP and artisan
        $php = '/opt/plesk/php/8.2/bin/php';
        $artisan = base_path('artisan');

        $process = new Process([$php, $artisan, 'reverb:start']);
        $process->setTimeout(null); // Allow long running process
        $process->setTty(false);    // Important: TTY must be false on Plesk/shared hosting

        $this->info('Starting Reverb server...');
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });
    }
}
