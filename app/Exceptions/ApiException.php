<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponseTrait;

class ApiException extends Exception
{
    use ApiResponseTrait;

    protected $statusCode;
    protected $errors;

    public function __construct(string $message = "", int $statusCode = 400, $errors = null, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    public function render($request)
    {
        return $this->errorResponse(
            $this->getMessage(),
            $this->statusCode,
            $this->errors
        );
    }

    public static function notFound(string $resource = "Ressource")
    {
        return new static("{$resource} non trouvé(e)", 404);
    }

    public static function unauthorized(string $message = "Accès non autorisé")
    {
        return new static($message, 403);
    }

    public static function validationError(array $errors)
    {
        return new static("Erreur de validation", 422, $errors);
    }

    public static function badRequest(string $message = "Requête invalide")
    {
        return new static($message, 400);
    }

    public static function forbidden(string $message = "Action interdite")
    {
        return new static($message, 403);
    }
}