<?php

namespace Awok\Console\Commands;

use Awok\Console\Generators\ControllerGenerator;
use Exception;
use Illuminate\Console\Command;

class ControllerMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:controller {controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new controller';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $generator = new ControllerGenerator();
        $name      = $this->argument('controller');
        try {
            $controller = $generator->generate($name);
            $this->info('Controller class created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$controller.'</comment>'."\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}