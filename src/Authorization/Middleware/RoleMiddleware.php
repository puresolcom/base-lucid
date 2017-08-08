<?php

namespace Awok\Authorization\Middleware;

use Awok\Authorization\Exceptions\UnauthorizedAccess;

class RoleMiddleware
{
    public function handle($request, \Closure $next, $role, $requireAll = false)
    {
        $roles = explode('|', $role);
        if (! app('authorization')->hasRole($roles, $requireAll)) {
            throw new UnauthorizedAccess();
        }

        return $next($request);
    }
}