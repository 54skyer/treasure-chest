<?php

namespace TreasureChest\Traits\Laravel\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ArgsValidate
{

    /**
     * @param         $request
     * @param Closure $next
     *
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        if (!$validator = $this->getValidator($request)) {
            return $next($request);
        }

        $rules    = Arr::get($validator, 'rules', []);
        $messages = Arr::get($validator, 'messages', []);

        $validate = Validator::make($request->all(), $rules, $messages);

        if ($validate->fails()) {
            throw new ValidationException($validate);
        }

        return $next($request);
    }

    /**
     * @param $request
     *
     * @return bool|mixed
     */
    protected function getValidator($request)
    {
        list($controller, $method) = explode('@', ($request->route()->action)['uses']);

        $class = str_replace('Controller', 'Validation', $controller);

        if (!class_exists($class) || !method_exists($class, $method)) {
            return false;
        }

        return call_user_func([new $class, $method]);
    }
}