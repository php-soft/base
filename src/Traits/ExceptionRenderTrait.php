<?php

namespace PhpSoft\Base\Traits;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use PhpSoft\Base\Exceptions\PaymentException;
use PhpSoft\Base\Exceptions\UnprocessableEntityException;

trait ExceptionRenderTrait
{

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $data = [
            'status' => $e instanceof HttpException ? $e->getStatusCode() : 500,
            'title'  => 'Internal Server Error',
            'errors' => [[
                'title'  => 'Internal Server Error',
                'detail' => $e->getMessage()?:('An exception of '.get_class_name($e)),
            ]]
        ];

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $data = array_merge($data, [
                'status' => 403,
                'errors' => [[
                    'title'  => 'Permission denied',
                    'detail' => $e->getMessage() ?: 'You are not allowed to perform this action',
                ]]
            ]);
        }

        if ($e instanceof \Illuminate\Validation\UnauthorizedException) {
            $data = array_merge($data, [
                'status' => 401,
                'errors' => [[
                    'title'  => 'Authenticate Error',
                    'detail' => $e->getMessage() ?: 'Unauthorized',
                ]]
            ]);
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            $data = array_merge($data, [
                'status' => 401,
                'errors' => [[
                    'title'  => 'Authenticate Error',
                    'detail' => $e->getMessage() ?: 'Unauthorized',
                ]]
            ]);
        }

        if ($e instanceof ModelNotFoundException) {
            $data = array_merge($data, [
                'status' => 404,
                'errors' => [[
                    'title'  => 'Not Found Error',
                    'detail' => 'Resource Not Found',
                ]]
            ]);
        }

        if ($e instanceof HttpException) {
            $data = array_merge($data, [
                'status' => $e->getStatusCode(),
                'errors' => [[
                    'title'  => 'Request Error',
                    'detail' => $e->getMessage()?:('An exception of '.get_class_name($e)),
                ]]
            ]);
        }

        if ($e instanceof NotFoundHttpException) {
            $data = array_merge($data, [
                'status' => $e->getStatusCode(),
                'errors' => [[
                    'title'  => 'Not Found Error',
                    'detail' => 'Route Not Found',
                ]]
            ]);
        }

        if ($e instanceof HttpResponseException) {
            $data = array_merge($data, [
                'status' => $e->getResponse()->status(),
                'title'  => 'Validation Error',
            ]);

            $errorResponses = function ($errors) use ($data) {
                foreach ($errors as $key => $error) {
                    if (!is_array($error)) {
                        $errorResponses[] = [
                            'title'  => 'Bad Request',
                            'detail' => $error,
                        ];
                    } else {
                        foreach ($error as $detail) {
                            $errorResponses[] = [
                                'title'  => $data['title'],
                                'detail' => $detail,
                                'source' => [
                                    'pointer' => $key
                                ]
                            ];
                        }
                    }
                }
                return $errorResponses;
            };
            $data['errors'] = $errorResponses((array)$e->getResponse()->getData());
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $data = array_merge($data, [
                'status' => 400,
                'title'  => 'Validation Error',
            ]);

            $errorResponses = function ($errors) use ($data) {
                foreach ($errors as $key => $error) {
                    if (!is_array($error)) {
                        $errorResponses[] = [
                            'title'  => 'Bad Request',
                            'detail' => $error,
                        ];
                    } else {
                        foreach ($error as $detail) {
                            $errorResponses[] = [
                                'title'  => $data['title'],
                                'detail' => $detail,
                                'source' => [
                                    'pointer' => $key
                                ]
                            ];
                        }
                    }
                }
                return $errorResponses;
            };
            $data['errors'] = $errorResponses($e->validator->errors()->toArray());
        }

        if ($e instanceof UnprocessableEntityException) {
            $data = array_merge($data, [
                'status' => 422,
                'errors' => [[
                    'title'  => 'Unprocessable Entity',
                    'detail' => $e->getMessage(),
                ]]
            ]);
        }

        if ($e instanceof PaymentException) {
            $data = array_merge($data, [
                'status' => 402,
                'errors' => [[
                    'title'  => 'Payment Required',
                    'detail' => $e->getMessage(),
                ]]
            ]);
        }

        if (config('app.env') === 'testing' && config('app.debug')) {
            dump('(debug)'. get_class_name($e) . ': '. $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        }

        return response()->json(arrayView('errors.exception', $data), $data['status']);
    }
}
