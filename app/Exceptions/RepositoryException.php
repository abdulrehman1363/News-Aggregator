<?php

namespace App\Exceptions;

use Exception;

class RepositoryException extends Exception
{
    public static function queryFailed(string $repository, string $method, \Throwable $previous = null): self
    {
        return new self(
            "Unable to retrieve data. Please try again later.",
            500,
            $previous
        );
    }

    public static function notFound(string $model, int|string $id): self
    {
        return new self(
            "The requested resource was not found.",
            404
        );
    }

    public static function createFailed(string $model, \Throwable $previous = null): self
    {
        return new self(
            "Unable to save data. Please try again.",
            500,
            $previous
        );
    }

    public static function updateFailed(string $model, int|string $id, \Throwable $previous = null): self
    {
        return new self(
            "Unable to update resource. Please try again.",
            500,
            $previous
        );
    }

    public static function deleteFailed(string $model, int|string $id, \Throwable $previous = null): self
    {
        return new self(
            "Unable to delete resource. Please try again.",
            500,
            $previous
        );
    }
}
