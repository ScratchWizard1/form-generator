<?php

declare(strict_types=1);

namespace FormGenerator\ErrorHandling;

use Exception;

class FormGeneratorException extends Exception
{
    private string $errorId;
    private string $fileOrigin;
    private int $lineOrigin;

    /**
     * Constructor for FormGeneratorException.
     *
     * @param string $message
     * @param int $code
     * @param string $errorId
     * @param string $fileOrigin
     * @param int $lineOrigin
     */
    public function __construct(
        string $message,
        int $code,
        string $errorId,
        string $fileOrigin = 'unknown',
        int $lineOrigin = 0
    ) {
        parent::__construct($message, $code);
        $this->errorId = $errorId;
        $this->fileOrigin = $fileOrigin;
        $this->lineOrigin = $lineOrigin;
    }

    /**
     * Getters for exception properties.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->errorId;
    }

    /**
     * Get the file origin of the exception.
     *
     * @return string
     */
    public function getFileOrigin(): string
    {
        return $this->fileOrigin;
    }

    /**
     * Get the line origin of the exception.
     *
     * @return int
     */
    public function getLineOrigin(): int
    {
        return $this->lineOrigin;
    }


    /**
     * Convert the exception details to an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'id' => $this->errorId,
            'file' => $this->fileOrigin,
            'line' => $this->lineOrigin,
        ];
    }
}
