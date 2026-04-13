<?php

declare(strict_types=1);

namespace FormGenerator\Validation;

use FormGenerator\Config\Globals;

class ValidationResult
{
    /**
     * @var array<mixed>
     */
    private array $data;

    /**
     * Constructor for ValidationResult.
     *
     * @param array<mixed> $data
     */
    public function __construct(
        array $data,
    ) {
        $this->data = $data;
    }

    /**
     * Get the validation data.
     *
     * @return array<mixed>|null
     */
    public function getData(): array|null
    {
        if (empty($this->data)) {
            return null;
        }
        return $this->data;
    }

    /**
     * Get the validation data as JSON.
     *
     * @return string|null
     */
    public function getJson(): string|null
    {
        if (empty($this->data)) {
            return null;
        }

        $json = json_encode($this->data);
        if ($json === false) {
            return null;
        }

        return $json;
    }

    /**
     * Check if the validation result is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (empty($this->data)) {
            return false;
        }
        if (isset($this->data[Globals::VALIDATION_ERROR_MARKER])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation error data.
     *
     * @return array<mixed>|null
     */
    public function getError(): array|null
    {
        if (empty($this->data)) {
            return null;
        }
        if (!$this->isValid()) {
            unset($this->data[Globals::VALIDATION_ERROR_MARKER]);
            return $this->data;
        } else {
            return null;
        }
    }
}
