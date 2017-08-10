<?php

namespace Awok\Console\Generators;

use Exception;
use Illuminate\Support\Str;

class OperationGenerator extends Generator
{
    protected $stubName = 'operation.plain.stub';

    public function generate($name)
    {
        $name = Str::studly($name).'Operation';
        $path = $this->findOperationPath($name);
        if ($this->exists($path)) {
            throw new Exception('Operation already exists!');
        }

        $namespace       = $this->findOperationNamespace();
        $vendorNamespace = $this->findVendorRootNameSpace();
        $this->setGeneratedClassName($name);
        $this->setGeneratedClassFQN($namespace.'\\'.$name);
        $content = file_get_contents($this->getStub());
        $content = str_replace(
            ['{{operation}}', '{{namespace}}', '{{vendor_namespace}}'],
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