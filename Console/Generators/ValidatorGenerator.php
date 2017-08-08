<?php

namespace Awok\Console\Generators;

use Exception;
use Illuminate\Support\Str;

class ValidatorGenerator extends Generator
{
    protected $stubName = 'validator.plain.stub';

    public function generate($name, $domain)
    {
        $name   = Str::studly($name).'Validator';
        $domain = Str::studly($domain);
        $path   = $this->findValidatorPath($name, $domain);
        if ($this->exists($path)) {
            throw new Exception('Validator already exists!');
        }

        $namespace       = $this->findValidatorNamespace($domain);
        $vendorNamespace = $this->findVendorRootNameSpace();
        $this->setGeneratedClassName($name);
        $this->setGeneratedClassFQN($namespace.'\\'.$name);
        $content = file_get_contents($this->getStub());
        $content = str_replace(
            ['{{validator}}', '{{namespace}}', '{{vendor_namespace}}'],
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