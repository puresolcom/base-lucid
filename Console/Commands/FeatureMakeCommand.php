<?php

namespace Awok\Console\Commands;

use Awok\Console\Generators\FeatureGenerator;
use Exception;
use Illuminate\Console\Command;

class FeatureMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:feature {feature}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new feature';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $generator = new FeatureGenerator();
        $name      = $this->argument('feature');
        try {
            $feature = $generator->generate($name);
            $this->info('Feature class created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$feature.'</comment>'."\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}