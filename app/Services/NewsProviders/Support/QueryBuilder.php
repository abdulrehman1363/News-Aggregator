<?php

namespace App\Services\NewsProviders\Support;

class QueryBuilder
{
    private array $params = [];

    /**
     * Add a parameter to the query
     */
    public function add(string $key, mixed $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Add a parameter only if the value is not empty
     */
    public function addIfPresent(string $key, mixed $value): self
    {
        if (!empty($value)) {
            $this->params[$key] = $value;
        }
        return $this;
    }

    /**
     * Get all parameters as array
     */
    public function toArray(): array
    {
        return $this->params;
    }

    /**
     * Create a new instance
     */
    public static function make(): self
    {
        return new self();
    }
}
