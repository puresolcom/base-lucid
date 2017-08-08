<?php

namespace Awok\Foundation\Eloquent;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Awok\Foundation\Eloquent\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * @param integer|null $limit
     * @param string       $dataKey
     * @param              $results
     *
     * @return mixed
     */
    protected function paginateResult($limit, $dataKey, $results)
    {
        // Paginate
        $limit = is_null($limit) ? config('pagination.limit', 20) : $limit;

        return $results->paginate($limit, ['*'], $pageName = 'page', $page = null, $dataKey);
    }
}