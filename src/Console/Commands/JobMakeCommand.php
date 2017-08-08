<?php

namespace Awok\Console\Commands;

use Awok\Console\Generators\JobGenerator;
use Exception;
use Illuminate\Console\Command;

class JobMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:job {job} {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new job';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $generator = new JobGenerator();
        $name      = $this->argument('job');
        $domain    = $this->argument('domain');
        try {
            $job = $generator->generate($name, $domain);
            $this->info('Job class created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$job.'</comment>'."\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}