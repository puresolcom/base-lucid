<?php

namespace Awok\Foundation\Exceptions\Handler;

use Awok\Domains\Http\Jobs\JsonErrorResponseJob;
use Awok\Foundation\Traits\JobDispatcherTrait;
use Awok\Foundation\Traits\MarshalTrait;
use Exception;
use Laravel\Lumen\Exceptions\Handler;

class JsonExceptionsHandler extends Handler
{
    use MarshalTrait;
    use JobDispatcherTrait;

    public function report(Exception $e)
    {
        parent::report($e);
    }

    public function render($request, Exception $e)
    {
        if (env('APP_DEBUG') == true && $request->has('debug')) {
            return parent::render($request, $e);
        }

        return $this->run(JsonErrorResponseJob::class, [
            'message' => $e->getMessage(),
            'code'    => get_class($e),
            'status'  => ($e->getCode() < 100 || $e->getCode() >= 600) ? 400 : $e->getCode(),
        ]);
    }
}