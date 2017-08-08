<?php

namespace Awok\Domains\Http\Jobs;

use Awok\Foundation\Http\Request;
use Awok\Foundation\Job;

class InputFilterJob extends Job
{
    protected $expectedKeys = [];

    public function __construct($expectedKeys = [])
    {
        if (! empty($expectedKeys)) {
            $this->expectedKeys = $expectedKeys;
        }
    }

    public function handle(Request $request)
    {
        if (empty($this->expectedKeys)) {
            return $request->all();
        }

        return $request->expect($this->expectedKeys);
    }
}