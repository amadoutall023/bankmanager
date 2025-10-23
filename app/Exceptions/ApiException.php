<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Traits\ApiResponseTrait;

class ApiException extends Exception
{
    use ApiResponseTrait;

    protected $errors;
    protected $statusCode;

    public function __construct(string $message = "", int $statusCode = 400, $errors = null, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        return $this->errorResponse($this->getMessage(), $this->statusCode, $this->errors);
    }
}
