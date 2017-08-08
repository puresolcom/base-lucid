<?php

namespace Awok\Domains\Data\Jobs;

use Awok\Domains\Data\Traits\EloquentRequestQueryable;
use Awok\Foundation\Http\Request;
use Awok\Foundation\Http\RequestFilterCollection;
use Awok\Foundation\Job;

class FindEloquentObjectFromRequestJob extends Job
{
    use EloquentRequestQueryable;

    protected $model;

    protected $primaryKey;

    protected $objectID;

    public function __construct($model, int $objectID, $primaryKey = 'id')
    {
        $this->model      = $model;
        $this->primaryKey = $primaryKey;
        $this->objectID   = $objectID;
    }

    public function handle(Request $request)
    {
        $this->setModel($this->model);
        $this->captureRequestQuery($request);
        // Filtering is not allowed in case of single object queries
        $this->setFilters(new RequestFilterCollection());
        $result = $this->buildQuery()->where($this->primaryKey, '=', $this->objectID);

        return $result->firstOrFail();
    }
}