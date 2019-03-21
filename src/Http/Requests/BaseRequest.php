<?php

namespace PhpSoft\Base\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

abstract class BaseRequest extends FormRequest
{
    // public function response(array $errors)
    // {
    //     return new JsonResponse($errors, 400);
    // }

    // protected function failedAuthorization()
    // {
    //     throw new HttpResponseException(new JsonResponse(['You have not permission to access this.'], 403));
    // }
}
