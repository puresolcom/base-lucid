<?php

namespace Awok\Console\Generators;

use Exception;
use Illuminate\Support\Str;

class ModelGenerator extends Generator
{
    protected $stubName = 'model.plain.stub';

    public function generate($name)
    {
        $name = Str::studly($name);
        $path = $this->findModelPath($name);
        if ($this->exists($path)) {
            throw new Exception('Model already exists!');
        }

        $namespace       = $this->findModelNamespace();
        $vendorNamespace = $this->findVendorRootNameSpace();
        $this->setGeneratedClassName($name);
        $this->setGeneratedClassFQN($namespace.'\\'.$name);
        $content = file_get_contents($this->getStub());
        $content = str_replace(
            ['{{model}}', '{{namespace}}', '{{vendor_namespace}}'],
            [$name, $namespace, $vendorNamespace],
            $content
        );
        $content = $this->applyStubVars($content);
        $this->createFile($path, $content);

        return $this->relativeFromReal($path);
    }

    protected function getStub()
    {
        return __DIR__.'/stubs/'.$this->getStubName();
    }
}