<?php

namespace PhpSoft\Base\Traits;

trait GetNextMiddleware
{
    public function next($next, $pass = false)
    {
        if ($pass) {
            request()->prevMiddlewareResult = $pass;
        }
        if (request()->prevMiddlewareResult) {
            return $next(request());
        }

        $nexts = array_get((array)app()->router->current(), 'computedMiddleware', []);
        $middlewares = app()->router->getMiddleware();
        array_walk($nexts, function (&$item) use($middlewares) { $item = $middlewares[explode(':', $item)[0]]??null; });
        $nexts = array_slice($nexts, array_search(get_class($this), $nexts) + 1);
        $nextClass = array_first($nexts);
        if (resolve($nextClass) instanceOf \PhpSoft\Base\Contracts\Http\Middleware\CanNextMiddlewareContract) {
            return $next(request());
        }
        throwForbiddenException();
    }
}
