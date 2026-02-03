<?php

namespace App\Exceptions;

use Exception;

class ServiceException extends Exception
{
    public static function operationFailed(string $operation, \Throwable $previous = null): self
    {
        return new self(
            "Operation failed. Please try again later.",
            500,
            $previous
        );
    }

    public static function invalidData(string $message): self
    {
        return new self($message, 422);
    }

    public static function unauthorized(string $message = 'You are not authorized to perform this action.'): self
    {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Access to this resource is forbidden.'): self
    {
        return new self($message, 403);
    }

    public static function notFound(string $message): self
    {
        return new self($message, 404);
    }
}
