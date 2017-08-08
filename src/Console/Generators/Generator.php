<?php

namespace Awok\Console\Generators;

use Exception;
use Illuminate\Filesystem\Filesystem;

class Generator
{
    protected $srcDirectoryName = 'app';

    protected $stubName;

    protected $stubVars = [];

    protected $generatedClassName;

    protected $generatedClassFQN;

    /**
     * @return mixed
     */
    public function getGeneratedClassName()
    {
        return $this->generatedClassName;
    }

    /**
     * @param mixed $generatedClassName
     */
    protected function setGeneratedClassName($generatedClassName)
    {
        $this->generatedClassName = $generatedClassName;
    }

    public function findControllerPath($controller)
    {
        return base_path('app').'/Http/Controllers/'.$controller.'.php';
    }

    public function findModelPath($model)
    {
        return base_path('app').'/Data/Models/'.$model.'.php';
    }

    public function findFeaturePath($feature)
    {
        return base_path('app').'/Features/'.$feature.'.php';
    }

    public function findJobPath($job, $domain)
    {
        return base_path('app').'/Domains/'.$domain.'/Jobs/'.$job.'.php';
    }

    public function findValidatorPath($validator, $domain)
    {
        return base_path('app').'/Domains/'.$domain.'/Validators/'.$validator.'.php';
    }

    public function findVendorRootNameSpace()
    {
        return 'Awok';
    }

    public function findControllerNamespace()
    {
        return $this->findRootNamespace().'\\Http\\Controllers';
    }

    public function findRootNamespace()
    {
        // read composer.json file contents to determine the namespace
        $composer = json_decode(file_get_contents(base_path().'/composer.json'), true);
        // see which one refers to the "src/" directory
        foreach ($composer['autoload']['psr-4'] as $namespace => $directory) {
            if ($directory === $this->getSourceDirectoryName().'/') {
                return trim($namespace, '\\');
            }
        }
        throw new Exception('App namespace not set in composer.json');
    }

    public function getSourceDirectoryName()
    {
        if (file_exists(base_path().'/'.$this->srcDirectoryName)) {
            return $this->srcDirectoryName;
        }

        return 'app';
    }

    public function findModelNamespace()
    {
        return $this->findRootNamespace().'\\Data\\Models';
    }

    public function findFeatureNamespace()
    {
        return $this->findRootNamespace().'\\Features';
    }

    public function findJobNamespace($domain)
    {
        return $this->findRootNamespace().'\\Domains\\'.$domain.'\\Jobs';
    }

    public function findValidatorNamespace($domain)
    {
        return $this->findRootNamespace().'\\Domains\\'.$domain.'\\Validators';
    }

    public function exists($path)
    {
        return file_exists($path);
    }

    public function createFile($path, $contents = '', $lock = false)
    {
        $this->createDirectory(dirname($path));

        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    public function createDirectory($path, $mode = 0755, $recursive = true, $force = true)
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    public function delete($path)
    {
        $fileSystem = new Filesystem();

        return $fileSystem->delete($path);
    }

    public function controllerName($name)
    {
        return studly_case(preg_replace('/Controller(\.php)?$/', '', $name).'Controller');
    }

    public function getStubName()
    {
        return $this->stubName;
    }

    public function setStubName($stubName)
    {
        $this->stubName = $stubName;
    }

    public function setStubVar($key, $value)
    {
        $this->stubVars[$key] = $value;
    }

    public function getStubVarValue($key)
    {
        if (! isset($this->stubVars[$key])) {
            return false;
        }

        return $this->stubVars[$key];
    }

    public function getGeneratedClassFQN()
    {
        return $this->generatedClassFQN;
    }

    protected function setGeneratedClassFQN($FQN)
    {
        $this->generatedClassFQN = $FQN;
    }

    protected function relativeFromReal($path, $needle = '')
    {
        if (! $needle) {
            $needle = $this->getSourceDirectoryName().'/';
        }

        return strstr($path, $needle);
    }

    /**
     * @param $content
     *
     * @return mixed
     */
    protected function applyStubVars($content)
    {
        $stubVars = $this->getStubVars();
        foreach ($stubVars as $k => $v) {
            $content = str_replace('{{'.$k.'}}', trim($v), $content);
        }

        return preg_replace('/\{\{.+\}\}/', '', $content);
    }

    public function getStubVars()
    {
        return $this->stubVars;
    }
}