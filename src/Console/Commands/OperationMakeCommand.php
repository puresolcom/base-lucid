<?php

namespace Awok\Console\Commands;

use Awok\Console\Generators\OperationGenerator;
use Exception;
use Illuminate\Console\Command;

class OperationMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:operation {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new operation';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $generator = new OperationGenerator();
        $name      = $this->argument('operation');
        try {
            $operation = $generator->generate($name);
            $this->info('Operation class created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$operation.'</comment>'."\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}