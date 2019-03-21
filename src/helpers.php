<?php

/**
 * Global helpers file with misc functions
 *
 * @author: Pham Cong Toan
 * @date: 2018-08-15 10:46:19
 */


if (!function_exists('throwError')) {
    /**
     * @deprecated
     * @return void
     */
    function throwError($message, $code)
    {
        if (!is_array($message)) {
            $message = [$message];
        }
        throw new \Illuminate\Http\Exception\HttpResponseException(new \Illuminate\Http\JsonResponse($message, $code));
    }
}

if (!function_exists('throwException')) {
    function throwException($message, $code)
    {
        throw new \Exception($message, $code);
    }
}

if (!function_exists('throwUnknownException')) {
    function throwUnknownException($message = 'Unknown Error')
    {
        throw new \PhpSoft\Base\Exceptions\UnknownException($message);
    }
}

if (!function_exists('throwAuthenticateException')) {
    function throwAuthenticateException($message = 'Unauthorized')
    {
        throw new \PhpSoft\Base\Exceptions\AuthenticationException($message);
    }
}

if (!function_exists('throwForbiddenException')) {
    function throwForbiddenException($message = 'You are not allowed to perform this action')
    {
        throw new \Illuminate\Auth\Access\AuthorizationException($message);
    }
}

if (!function_exists('throwUnprocessableException')) {
    function throwUnprocessableException($message = 'Unprocessable Entity')
    {
        throw new \PhpSoft\Base\Exceptions\UnprocessableEntityException($message);
    }
}

if (!function_exists('throwPaymentException')) {
    function throwPaymentException($message = 'Payment Required')
    {
        throw new \PhpSoft\Base\Exceptions\PaymentException($message);
    }
}

if (!function_exists('get_class_name')) {
    function get_class_name($object)
    {
        $classname = is_string($object) ? $object : get_class($object);
        if ($pos = strrpos($classname, '\\')) {
            return substr($classname, $pos + 1);
        }
        return $classname;
    }
}

if (!function_exists('images_path')) {
    function images_path(string $path = '')
    {
        return storage_path('app/public/images'.str_start($path, '/'));
    }
}

if (!function_exists('isNull')) {
    function isNull($value)
    {
        return $value === 'null' || blank($value);
    }
}

if (!function_exists('money')) {
    function money($number, $currency, $isPrefix = true)
    {
        // $currency = 'vnd';
        $currency = strtoupper($currency);
        $locale = [
            'USD' => 'en_US',
            'AUD' => 'en_AU',
            'VND' => 'vi_VN',
        ];
        $default = 'en_AU';
        $fmt = numfmt_create(array_get($locale, $currency, $default), NumberFormatter::CURRENCY);
        return $currency . ' ' . $fmt->formatCurrency($number, $currency);
    }
}

if (!function_exists('dtf')) {
    function dtf($dt, $fm = 'Y-m-d H:i:s', $tz = 'Australia/Melbourne')
    {
        if (is_string($dt)) {
            $dt = new \Carbon\Carbon($dt);
        }
        if (empty($tz)) {
            $tz = 'Australia/Melbourne';
        }
        if (empty($fm)) {
            $fm = 'Y-m-d H:i:s';
        }
        $dt->setTimezone($tz);
        return $dt->format($fm);
    }
}

if (!function_exists('df')) {
    function df($dt, $fm = 'Y-m-d', $tz = null)
    {
        return dtf($dt, $fm, $tz);
    }
}

if (!function_exists('call_if')) {
    function call_if(\Closure $call, $if = null)
    {
        if ($if === true) {
            return call_user_func($call);
        }
        if (is_callable($if)) {
            $condition = call_user_func($if);
            if ($condition) {
                return call_user_func($call);
            }
        }
    }
}
