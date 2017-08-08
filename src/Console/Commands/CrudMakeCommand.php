<?php

namespace Awok\Console\Commands;

use Awok\Console\Generators\ControllerGenerator;
use Awok\Console\Generators\FeatureGenerator;
use Awok\Console\Generators\JobGenerator;
use Awok\Console\Generators\ModelGenerator;
use Awok\Console\Generators\ValidatorGenerator;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class CrudMakeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:crud {singular_entity_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a CRUD for an entity';

    protected $guardedDirectoryNames = ['Features', 'Models', 'Controllers', 'Domains', 'Events'];

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $name       = $this->argument('singular_entity_name');
        $pluralName = Str::plural($name);

        $controllerGenerator = new ControllerGenerator();
        $modelGenerator      = new ModelGenerator();
        $featureGenerator    = new FeatureGenerator();

        $generatedFilesPaths = [];

        try {
            $this->info('Beginning creating CRUD.'."\n");
            /*****************************
             * Generate Model
             ****************************/
            $generatedFilesPaths[] = $modelGenerator->generate($name);
            /*****************************
             * Generate Validators
             ****************************/
            $createValidatorGenerator = new ValidatorGenerator();
            $updateValidatorGenerator = new ValidatorGenerator();
            $generatedFilesPaths[]    = $createValidatorGenerator->generate("create_{$name}", $name);
            $generatedFilesPaths[]    = $updateValidatorGenerator->generate("update_{$name}", $name);
            /*****************************
             * Generate Jobs
             ****************************/
            // Create Job
            $createJobGenerator = new JobGenerator();
            $createJobGenerator->setStubName('job.stub');
            $createJobGenerator->setStubVar('namespace_below',
                "use {$modelGenerator->getGeneratedClassFQN()};\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Foundation\\Job;\n"
            );
            $createJobGenerator->setStubVar('parent_job', 'Job');
            $createJobGenerator->setStubVar('class_members', 'protected $data;');
            $createJobGenerator->setStubVar('constructor_arguments', 'array $input');
            $createJobGenerator->setStubVar('constructor_body', '$this->data = $input;');
            $createJobGenerator->setStubVar('handler_arguments', "{$modelGenerator->getGeneratedClassName()} \$model");
            $createJobGenerator->setStubVar('handler_body', 'return $model->create($this->data);');
            $generatedFilesPaths[] = $createJobGenerator->generate("create_{$name}", $name);
            $createJobFQN          = $createJobGenerator->getGeneratedClassFQN();
            $createJobClassName    = $createJobGenerator->getGeneratedClassName();

            // Create Input Validate Job
            $updateInputValidateJobGenerator = new JobGenerator();
            $updateInputValidateJobGenerator->setStubName('job.stub');
            $updateInputValidateJobGenerator->setStubVar('namespace_below',
                "use {$createValidatorGenerator->getGeneratedClassFQN()};\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Foundation\\Job;\n"
            );
            $updateInputValidateJobGenerator->setStubVar('parent_job', 'Job');
            $updateInputValidateJobGenerator->setStubVar('class_members', 'protected $input;');
            $updateInputValidateJobGenerator->setStubVar('constructor_arguments', 'array $input');
            $updateInputValidateJobGenerator->setStubVar('constructor_body', '$this->input = $input;');
            $updateInputValidateJobGenerator->setStubVar('handler_arguments', "{$createValidatorGenerator->getGeneratedClassName()} \$validator");
            $updateInputValidateJobGenerator->setStubVar('handler_body', 'return $validator->validate($this->input);');

            $generatedFilesPaths[]           = $updateInputValidateJobGenerator->generate("create_{$name}_input_validate", $name);
            $createInputValidateJobFQN       = $updateInputValidateJobGenerator->getGeneratedClassFQN();
            $createInputValidateJobClassName = $updateInputValidateJobGenerator->getGeneratedClassName();

            // Create Input Filter Job
            $createInputFilterJobGenerator = new JobGenerator();
            $createInputFilterJobGenerator->setStubName('job.stub');
            $createInputFilterJobGenerator->setStubVar('namespace_below',
                "use {$createInputFilterJobGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\InputFilterJob;\n".
                "use {$createInputFilterJobGenerator->findVendorRootNameSpace()}\\Foundation\\Http\\Request;\n"
            );
            $createInputFilterJobGenerator->setStubVar('parent_job', 'InputFilterJob');
            $createInputFilterJobGenerator->setStubVar('class_members', 'protected $expectedKeys = [/** @todo add array of expected input keys here */];');
            $createInputFilterJobGenerator->setStubVar('constructor_arguments', 'array $expectedKeys = []');
            $createInputFilterJobGenerator->setStubVar('constructor_body', 'parent::__construct($expectedKeys);');
            $createInputFilterJobGenerator->setStubVar('handler_arguments', "Request \$request");
            $createInputFilterJobGenerator->setStubVar('handler_body', 'return parent::handle($request);');
            $generatedFilesPaths[]         = $createInputFilterJobGenerator->generate("create_{$name}_input_filter", $name);
            $createInputFilterJobFQN       = $createInputFilterJobGenerator->getGeneratedClassFQN();
            $createInputFilterJobClassName = $createInputFilterJobGenerator->getGeneratedClassName();

            // Update Job
            $updateJobGenerator = new JobGenerator();
            $updateJobGenerator->setStubName('job.stub');
            $updateJobGenerator->setStubVar('namespace_below',
                "use {$modelGenerator->getGeneratedClassFQN()};\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Foundation\\Job;\n"
            );
            $updateJobGenerator->setStubVar('parent_job', 'Job');
            $updateJobGenerator->setStubVar('class_members',
                "protected \$model;\n".
                "\t".
                "protected \$input;\n"
            );
            $updateJobGenerator->setStubVar('constructor_arguments', "{$modelGenerator->getGeneratedClassName()} \$model, array \$input");
            $updateJobGenerator->setStubVar('constructor_body',
                "\$this->model     = \$model;\n".
                "\t\t".
                "\$this->input     = \$input;\n"
            );
            $updateJobGenerator->setStubVar('handler_arguments', '');
            $updateJobGenerator->setStubVar('handler_body', 'return $this->model->update($this->input) ? $this->model : false;');
            $generatedFilesPaths[] = $updateJobGenerator->generate("update_{$name}", $name);
            $updateJobFQN          = $updateJobGenerator->getGeneratedClassFQN();
            $updateJobClassName    = $updateJobGenerator->getGeneratedClassName();

            // Update Input Validate Job
            $updateInputValidateJobGenerator = new JobGenerator();
            $updateInputValidateJobGenerator->setStubName('job.stub');
            $updateInputValidateJobGenerator->setStubVar('namespace_below',
                "use {$updateValidatorGenerator->getGeneratedClassFQN()};\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Foundation\\Job;\n"
            );
            $updateInputValidateJobGenerator->setStubVar('parent_job', 'Job');
            $updateInputValidateJobGenerator->setStubVar('class_members', 'protected $input;');
            $updateInputValidateJobGenerator->setStubVar('constructor_arguments', 'array $input');
            $updateInputValidateJobGenerator->setStubVar('constructor_body', '$this->input = $input;');
            $updateInputValidateJobGenerator->setStubVar('handler_arguments', "{$updateValidatorGenerator->getGeneratedClassName()} \$validator");
            $updateInputValidateJobGenerator->setStubVar('handler_body', 'return $validator->validate($this->input);');

            $generatedFilesPaths[]           = $updateInputValidateJobGenerator->generate("update_{$name}_input_validate", $name);
            $updateInputValidateJobFQN       = $updateInputValidateJobGenerator->getGeneratedClassFQN();
            $updateInputValidateJobClassName = $updateInputValidateJobGenerator->getGeneratedClassName();

            // Update Input Filter Job
            $updateInputFilterJobGenerator = new JobGenerator();
            $updateInputFilterJobGenerator->setStubName('job.stub');
            $updateInputFilterJobGenerator->setStubVar('namespace_below',
                "use {$updateInputFilterJobGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\InputFilterJob;\n".
                "use {$updateInputFilterJobGenerator->findVendorRootNameSpace()}\\Foundation\\Http\\Request;\n"
            );
            $updateInputFilterJobGenerator->setStubVar('parent_job', 'InputFilterJob');
            $updateInputFilterJobGenerator->setStubVar('class_members', 'protected $expectedKeys = [/** @todo add array of expected input keys here */];');
            $updateInputFilterJobGenerator->setStubVar('constructor_arguments', 'array $expectedKeys = []');
            $updateInputFilterJobGenerator->setStubVar('constructor_body', 'parent::__construct($expectedKeys);');
            $updateInputFilterJobGenerator->setStubVar('handler_arguments', "Request \$request");
            $updateInputFilterJobGenerator->setStubVar('handler_body', 'return parent::handle($request);');
            $generatedFilesPaths[]         = $updateInputFilterJobGenerator->generate("update_{$name}_input_filter", $name);
            $updateInputFilterJobFQN       = $updateInputFilterJobGenerator->getGeneratedClassFQN();
            $updateInputFilterJobClassName = $updateInputFilterJobGenerator->getGeneratedClassName();

            // Delete Job
            $deleteJobGenerator = new JobGenerator();
            $deleteJobGenerator->setStubName('job.stub');
            $deleteJobGenerator->setStubVar('namespace_below',
                "use {$modelGenerator->getGeneratedClassFQN()};\n".
                "use {$deleteJobGenerator->findVendorRootNameSpace()}\\Foundation\\Job;\n"
            );
            $deleteJobGenerator->setStubVar('parent_job', 'Job');
            $deleteJobGenerator->setStubVar('class_members', 'protected $model;');
            $deleteJobGenerator->setStubVar('constructor_arguments', "{$modelGenerator->getGeneratedClassName()} \$model");
            $deleteJobGenerator->setStubVar('constructor_body', '$this->model = $model;');
            $deleteJobGenerator->setStubVar('handler_body', 'return $this->model->delete();');
            $generatedFilesPaths[] = $deleteJobGenerator->generate("delete_{$name}", $name);
            $deleteJobFQN          = $deleteJobGenerator->getGeneratedClassFQN();
            $deleteJobClassName    = $deleteJobGenerator->getGeneratedClassName();
            /*****************************
             * Generate Features
             ****************************/
            $featureGenerator->setStubName('feature.stub');
            // List Feature
            $featureGenerator->setStubVar('namespace_below',
                "use {$featureGenerator->findVendorRootNameSpace()}\\Domains\\Data\\Jobs\\BuildEloquentQueryFromRequestJob;\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonResponseJob;\n".
                "use {$modelGenerator->getGeneratedClassFQN()};"
            );
            $featureGenerator->setStubVar('handler_body',
                "\$results = \$this->run(BuildEloquentQueryFromRequestJob::class, ['model' => {$modelGenerator->getGeneratedClassName()}::class]);".
                "\n\n".
                "\t\t".
                "return \$this->run(new JsonResponseJob(\$results));"
            );
            $generatedFilesPaths[] = $featureGenerator->generate("list_{$name}");
            $controllerGenerator->setStubVar('list_feature_namespace', $featureGenerator->getGeneratedClassFQN());
            $controllerGenerator->setStubVar('list_feature_class', $featureGenerator->getGeneratedClassName());

            // Get Feature
            $featureGenerator->setStubVar('namespace_below',
                "use {$featureGenerator->findVendorRootNameSpace()}\\Domains\\Data\\Jobs\\FindEloquentObjectFromRequestJob;\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonResponseJob;\n".
                "use {$modelGenerator->getGeneratedClassFQN()};"
            );
            $featureGenerator->setStubVar('class_members', 'protected $objectID;');
            $featureGenerator->setStubVar('constructor_arguments', 'int $objectID');
            $featureGenerator->setStubVar('constructor_body', '$this->objectID = $objectID;');
            $featureGenerator->setStubVar('handler_body',
                "\$model = \$this->run(FindEloquentObjectFromRequestJob::class, ['model' => {$modelGenerator->getGeneratedClassName()}::class, 'objectID' => \$this->objectID]);".
                "\n\n".
                "\t\t".
                "return \$this->run(new JsonResponseJob(\$model));"
            );
            $generatedFilesPaths[] = $featureGenerator->generate("get_{$name}");
            $controllerGenerator->setStubVar('get_feature_namespace', $featureGenerator->getGeneratedClassFQN());
            $controllerGenerator->setStubVar('get_feature_class', $featureGenerator->getGeneratedClassName());

            // Create Feature
            $featureGenerator->setStubVar('class_members', '');
            $featureGenerator->setStubVar('constructor_arguments', '');
            $featureGenerator->setStubVar('constructor_body', '');
            $featureGenerator->setStubVar('namespace_below',
                "use {$createInputValidateJobFQN};\n".
                "use {$createInputFilterJobFQN};\n".
                "use {$createJobFQN};\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonResponseJob;\n".
                "use {$featureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonErrorResponseJob;\n"
            );
            $featureGenerator->setStubVar('handler_body',
                "\n".
                "\t\t".
                "// Validate Request Inputs".
                "\n".
                "\t\t".
                "\$this->run({$createInputValidateJobClassName}::class, ['input' => \$request->all()]);".

                "\n\n".
                "\t\t".
                "// Exclude unwanted Inputs".
                "\n".
                "\t\t".
                "\$filteredInputs = \$this->run({$createInputFilterJobClassName}::class);".

                "\n\n".
                "\t\t".
                "// Create model".
                "\n".
                "\t\t".
                "\$created = \$this->run({$createJobClassName}::class, ['input' => \$filteredInputs]);".

                "\n\n".
                "\t\t".
                "// Response".
                "\n".
                "\t\t".
                "if (! \$created) { return \$this->run(new JsonErrorResponseJob('Unable to create {$modelGenerator->getGeneratedClassName()}')); }".
                "\n\n".
                "\t\t".
                "return \$this->run(new JsonResponseJob(\$created));"
            );
            $generatedFilesPaths[] = $featureGenerator->generate("create_{$name}");
            $controllerGenerator->setStubVar('create_feature_namespace', $featureGenerator->getGeneratedClassFQN());
            $controllerGenerator->setStubVar('create_feature_class', $featureGenerator->getGeneratedClassName());

            // Update feature
            $updateFeatureGenerator = new FeatureGenerator();
            $updateFeatureGenerator->setStubName('feature.stub');
            $updateFeatureGenerator->setStubVar('namespace_below',
                "use {$updateInputValidateJobFQN};\n".
                "use {$updateInputFilterJobFQN};\n".
                "use {$updateJobFQN};\n".
                "use {$updateFeatureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonResponseJob;\n".
                "use {$updateFeatureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonErrorResponseJob;\n".
                "use {$updateFeatureGenerator->findVendorRootNameSpace()}\\Domains\\Data\\Jobs\\FindObjectByIDJob;\n".
                "use {$modelGenerator->getGeneratedClassFQN()};\n"
            );

            $updateFeatureGenerator->setStubVar('class_members', 'protected $objectID;');
            $updateFeatureGenerator->setStubVar('constructor_arguments', 'int $objectID');
            $updateFeatureGenerator->setStubVar('constructor_body', '$this->objectID = $objectID;');
            $updateFeatureGenerator->setStubVar('handler_body',
                "\n".
                "\t\t".
                "// Validate Request Inputs".
                "\n".
                "\t\t".
                "\$this->run({$updateInputValidateJobClassName}::class, ['input' => \$request->all()]);".

                "\n".
                "\t\t".
                "// Finding model".
                "\n".
                "\t\t".
                "\$model = \$this->run(FindObjectByIDJob::class,  ['model' => {$modelGenerator->getGeneratedClassName()}::class, 'objectID' => \$this->objectID]);".

                "\n\n".
                "\t\t".
                "// Exclude unwanted Inputs".
                "\n".
                "\t\t".
                "\$filteredInputs = \$this->run({$updateInputFilterJobClassName}::class);".

                "\n\n".
                "\t\t".
                "// Update model".
                "\n".
                "\t\t".
                "\$updated = \$this->run({$updateJobClassName}::class, ['model' => \$model, 'input' => \$filteredInputs]);".

                "\n\n".
                "\t\t".
                "// Response".
                "\n".
                "\t\t".
                "if (! \$updated) { return \$this->run(new JsonErrorResponseJob('Unable to update {$modelGenerator->getGeneratedClassName()}')); }".
                "\n\n".
                "\t\t".
                "return \$this->run(new JsonResponseJob(\$updated));"
            );
            $generatedFilesPaths[] = $updateFeatureGenerator->generate("update_{$name}");
            $controllerGenerator->setStubVar('update_feature_namespace', $updateFeatureGenerator->getGeneratedClassFQN());
            $controllerGenerator->setStubVar('update_feature_class', $updateFeatureGenerator->getGeneratedClassName());
            // Delete feature
            $deleteFeatureGenerator = new FeatureGenerator();
            $deleteFeatureGenerator->setStubName('feature.stub');
            $deleteFeatureGenerator->setStubVar('namespace_below',
                "use {$deleteJobFQN};\n".
                "use {$updateFeatureGenerator->findVendorRootNameSpace()}\\Domains\\Data\\Jobs\\FindObjectByIDJob;\n".
                "use {$updateFeatureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonResponseJob;\n".
                "use {$updateFeatureGenerator->findVendorRootNameSpace()}\\Domains\\Http\\Jobs\\JsonErrorResponseJob;\n".
                "use {$modelGenerator->getGeneratedClassFQN()};\n"
            );

            $deleteFeatureGenerator->setStubVar('class_members', 'protected $objectID;');
            $deleteFeatureGenerator->setStubVar('constructor_arguments', 'int $objectID');
            $deleteFeatureGenerator->setStubVar('constructor_body', '$this->objectID = $objectID;');
            $deleteFeatureGenerator->setStubVar('handler_body',
                "\n".
                "\t\t".
                "// Finding model".
                "\n".
                "\t\t".
                "\$model = \$this->run(FindObjectByIDJob::class, ['model' => {$modelGenerator->getGeneratedClassName()}::class, 'objectID' => \$this->objectID]);".

                "\n\n".
                "\t\t".
                "// Deleting model".
                "\n".
                "\t\t".
                "\$deleted = \$this->run({$deleteJobClassName}::class, ['model' => \$model]);".

                "\n\n".
                "\t\t".
                "// Response".
                "\n".
                "\t\t".
                "if (! \$deleted) {return \$this->run(new JsonErrorResponseJob('Unable to delete {$modelGenerator->getGeneratedClassName()}'));}".
                "\n\n".
                "\t\t".
                "return \$this->run(new JsonResponseJob('{$modelGenerator->getGeneratedClassName()} Deleted Successfully'));"

            );
            $generatedFilesPaths[] = $deleteFeatureGenerator->generate("delete_{$name}");
            $controllerGenerator->setStubVar('delete_feature_namespace', $deleteFeatureGenerator->getGeneratedClassFQN());
            $controllerGenerator->setStubVar('delete_feature_class', $deleteFeatureGenerator->getGeneratedClassName());
            /*****************************
             * Generate Controllers
             ****************************/
            $controllerGenerator->setStubName('controller.stub');
            $generatedFilesPaths[] = $controllerGenerator->generate($pluralName);

            foreach ($generatedFilesPaths as $generatedFilePath) {
                $this->comment('File '.$generatedFilePath.' created');
            }
            $this->info("\n".'CRUD Generation done');
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->deleteFiles($generatedFilesPaths);
        }
    }

    protected function deleteFiles($paths)
    {
        $this->warn('Deleting files because of an error');
        $fileSystem = new Filesystem();

        foreach ($paths as $path) {
            if (! $fileSystem->delete($path)) {
                $this->error('Unable to delete file'.' '.$path."\n");
            } else {
                $this->warn('File: '.$path.' deleted'."\n");
            }

            $directory = $fileSystem->dirname($path);
            if (empty($fileSystem->files($directory)) && ! in_array($fileSystem->basename($directory), $this->guardedDirectoryNames)) {
                if ($fileSystem->deleteDirectory($directory)) {
                    $this->warn('Dir: '.$directory.' deleted'."\n");
                }
            }
        }

        $this->info('All files were deleted successfully');
    }
}