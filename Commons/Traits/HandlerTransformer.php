<?php


namespace Commons\Traits;

use Commons\Http\Resources\BaseResource;
use Exception;
use Illuminate\Validation\ValidationException;

trait HandlerTransformer
{
    protected function invalidJson($request, ValidationException $exception)
    {
        return BaseResource::errors(
            $exception->errors(),
            $exception->getMessage(),
            $exception->status
        )->response($request);
    }

    protected function prepareJsonResponse($request, Exception $e)
    {
        $errors = $this->convertExceptionToArray($e);
        $message = array_pull($errors, 'message');

        return BaseResource::errors(
            $errors,
            $message,
            $this->isHttpException($e) ? $e->getStatusCode() : 500
        )->response($request)->withHeaders($this->isHttpException($e) ? $e->getHeaders() : []);
    }
}