<?php

namespace Awok\Domains\Data\Jobs;

use Awok\Foundation\Job;

class FindObjectByIDJob extends Job
{
    protected $model;

    protected $primaryKey;

    protected $objectID;

    public function __construct($model, int $objectID, $primaryKey = 'id')
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $this->model      = $model;
        $this->primaryKey = $primaryKey;
        $this->objectID   = $objectID;
    }

    public function handle()
    {
        return $this->model->findOrFail($this->objectID);
    }
}