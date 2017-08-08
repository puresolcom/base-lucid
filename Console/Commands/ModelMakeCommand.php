<?php

namespace Awok\Console\Commands;

use Awok\Console\Generators\ModelGenerator;
use Exception;
use Illuminate\Console\Command;

class ModelMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:model {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new model';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $generator = new ModelGenerator();
        $name      = $this->argument('model');
        try {
            $model = $generator->generate($name);
            $this->info('Model class created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$model.'</comment>'."\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}