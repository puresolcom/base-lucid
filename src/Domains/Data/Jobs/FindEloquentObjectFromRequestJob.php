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

    protected $otherQueryParams;

    public function __construct($model, $objectID, $primaryKey = 'id', $otherQueryParams = [])
    {
        $this->model       = $model;
        $this->primaryKey  = $primaryKey;
        $this->objectID    = $objectID;
        $this->otherQueryParams = $otherQueryParams;
    }

    public function handle(Request $request)
    {
        $this->setModel($this->model);
        $this->captureRequestQuery($request);
        // Filtering is not allowed in case of single object queries
        $this->setFilters(new RequestFilterCollection());
        $result = $this->buildQuery()->where($this->primaryKey, '=', $this->objectID);
        if(!empty($this->otherQueryParams)) {
            foreach($this->otherQueryParams as $key => $value) {
                $result = $result->orWhere($key, '=', $value);
            }
        }

        return $result->firstOrFail();
    }
}