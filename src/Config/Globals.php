<?php

declare(strict_types=1);

namespace FormGenerator\Config;

use DateTime;
use DateTimeImmutable;

class Globals
{
    public const VALIDATION_CONFIG =
    "__VALIDATION_CONFIG_" .
        "A9xT7LmQ3bZpW2vN8rF6sK4yD1hJ0cU5eP2qR8tV6mB4nH9kG3jS7dL1fX5cA8uE2" .
        "wR7tY4pB9nM3xC6kQ1zT5fL8hD2jG9sV4mW7bP3rN6cF2uH8aJ1yK5eZ0qU4vS9g" .
        "Z8rD3mK1tP6qB9xH4gN7vC2fJ5yL8sE3wR1pU6kA9dT4bM7nF2hQ5cY8uS0jV3eG6" .
        "D3nH7aK5pL8qW2sX9cY4rF6tM1jV3uC7bZ5mT8eN2kP4gR1dS9hJ6fA0xU3yE8vQ";
    public const INTERNAL_EMPTY =
    "__INTERNAL_EMPTY_" .
        "Q4nV8mS2dL6pR9xT1gC7hK3bN5yW8fD2zM6aJ9tB4cH7rP3uG1kY5sF8wE2vL6nX0" .
        "pC9tR4yM7gB1hW3fD8kN2sZ6aT5vJ9cG4mQ1bH7uP2rL8xS5eF3nY9jK6dA0wU4V2" .
        "mW1tC5jN9sP3kH7aL2dV6bR8xG4uF1pY9cZ5qM3fT7gD2wJ8hS6nK1vE4rU0lA5B7" .
        "X7cJ1mT5eN8qF2gY9bP3sR6vK4uH0aD7xZ5lC8rM1jW4pS9tE3yG6wB2nL0kV8Q5";

    public const VALIDATION_ERROR_MARKER =
    "__VALIDATION_ERROR_" .
        "Z8rD3mK1tP6qB9xH4gN7vC2fJ5yL8sE3wR1pU6kA9dT4bM7nF2hQ5cY8uS0jV3eG6" .
        "aG9uC4nS7rW2fM8yP3bK6tX1dV5jL9qF2gZ7mE0cR4hT8kB3pY6wN1sU5vH9aD2J8" .
        "mW1tC5jN9sP3kH7aL2dV6bR8xG4uF1pY9cZ5qM3fT7gD2wJ8hS6nK1vE4rU0lA5B7" .
        "cQ9eH4yR1nM6sW3pK8gB2xF7uD0aJ5vL9tS4fZ8qE2hC6mN1rT3wG7kY5V0bP4U8";

    public const REGEX_REPLACE_VALUE =
    "__REGEX_REPLACE_VALUE_" .
        "M5wQ1yH7pD3xS9vL4rC8fB2tG6mK0uE7jF1cN5gW9sA3zR8kV2nY4hP6bJ0qT7dL1" .
        "A9xT7LmQ3bZpW2vN8rF6sK4yD1hJ0cU5eP2qR8tV6mB4nH9kG3jS7dL1fX5cA8uE2" .
        "aG9uC4nS7rW2fM8yP3bK6tX1dV5jL9qF2gZ7mE0cR4hT8kB3pY6wN1sU5vH9aD2J8" .
        "eM3rK7nL1cF9pX4bS8vG2tW0dJ6zT3gN5hQ8mC1uE7kA4yR9B2sP5V0qH6U8wF3D1";

    public const ARRAY_REPLACE_VALUE =
    "__ARRAY_REPLACE_VALUE_" .
        "T7LmQ3bZpW2vN8rF6sK4Q3bZpW2vN8rF6sK4yD1h4nH9kG3j2fM8yP3bK6tX1dV5j" .
        "Z8rD3mK1tP6qB9xH4gN7vC2fJ5yL8sE3wR1pU6kA9dT4bM7nF2hQ5cY8uS0jV3eG6" .
        "aG9uC4ngB2xF7uD0aJ5vL9tS4fZ8qE2hC6mNruEcR4hT8kB3pY6wN1sU5vH9aD2J8" .
        "wR7tY4pB9nM3xC6kQ1zT5fL8hD2jG9sV4mW7bP3rN6cF2uH8aJ1yK5eZ0qU4vS9gy";
    public const NOTNULL_REPLACE_VALUE =
    "__NOTNULL_REPLACE_VALUE_" .
        "M5wQ1yH7pD3xS9vL4rC8fB2tG6mK0uE7jF1cN5gW9sA3zR8kV2nY4hP6bJ0qT7dL1" .
        "A9xT7LmQ3bZpW2vN8rF6s2dV6bR8xG4uF1pY9cZ5qM3fT7gD2wJ8hS6nK1vE4rUz2" .
        "aG9u1nM6sW3pK8gB2xF7uD0aJ5vL9tS4fZ88kB3pY64hTkB3pY6wN1sU5vH9aD2J8" .
        "eM3rK7nL1cF9pX4bS8vG2tW0dJ6zT8rF6sK4yD1hJ0cU5ePee2sP5V0qH6U8wF3D1";

    /**
     * @var array<mixed> $array
     */
    private static array $array = [];

    /**
     * @var array<string,bool> $security
     */
    private static array $security = [];
    private static string $regex = '';
    private static string $acceptCharset = 'UTF-8';

    /**
     * Private constructor to prevent instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        ; // This constructor is private and empty; no code is executed here.
    }

    /**
     * Get the regex.
     *
     * @return string
     */
    public static function getRegex(): string
    {
        return self::$regex;
    }

    /**
     * Set the regex.
     *
     * @param string $value
     * @return void
     */
    public static function setRegex(string $value): void
    {
        self::$regex = $value;
    }

    /**
     * Define security flags (key => enabled).
     *
     * @param array<string,bool> $security
     * @return void
     */
    public static function security(array $security): void
    {
        self::$security = $security;
    }
    /**
     * Get the security flag value for the given key, or true.
     *
     * @param string $key
     * @return bool
     */
    public static function getSecurity(string $key): bool
    {
        if (empty(trim($key))) {
            return true;
        }
        foreach (self::$security as $subkey => $value) {
            if (trim($key) == trim($subkey)) {
                return $value;
            }
        }
        return true;
    }

    /**
     * Get the charset.
     *
     * @return string
     */
    public static function getCharset(): string
    {
        return self::$acceptCharset;
    }

    /**
     * Set the charset.
     *
     * @param string $value
     * @return void
     */
    public static function setCharset(string $value): void
    {
        self::$acceptCharset = $value;
    }


    /**
     * Set the global array.
     *
     * @param array<mixed> $value
     * @return void
     */
    public static function setArray(array $value): void
    {
        self::$array = $value;
    }

    /**
     * Get the global array.
     *
     * @return array<mixed>
     */
    public static function getArray(): array
    {
        return self::$array;
    }

    /**
     * Convert the given value to a string.
     *
     * @param mixed $data
     * @return string
     */
    public static function toStr(mixed $data): string
    {
        if (is_array($data)) {
            $data = implode(", ", $data);
        } elseif (is_bool($data)) {
            $data = $data ? 'true' : 'false';
        } elseif (is_null($data)) {
            $data = '';
        } elseif (is_numeric($data)) {
            $data = (string)$data;
        } else {
            if (
                is_scalar($data) ||
                (is_object($data) &&
                    method_exists($data, '__toString'))
            ) {
                $data = (string)$data;
            } else {
                $data = '';
            }
        }

        if (trim($data) === '' || $data === Globals::INTERNAL_EMPTY) {
            $data = '';
        }
        return $data;
    }

    /**
     * Convert mixed value to array<mixed>.
     *
     * @param mixed $data
     * @return array<mixed>
     */
    public static function toArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if ($data instanceof \Traversable) {
            return iterator_to_array($data);
        }

        if (is_object($data)) {
            return get_object_vars($data);
        }

        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [$data];
    }



    /**
     * Escape and format the given data for error messages.
     *
     * @param mixed $data
     * @return string
     */
    public static function esc(mixed $data): string
    {
        if ($data instanceof DateTime || $data instanceof DateTimeImmutable) {
            if ($data instanceof DateTime) {
                $data = "[DateTime{{$data->format(DateTime::ATOM)}}]";
            } else {
                $data = "[DateTimeImmutable{{$data->format(DateTime::ATOM)}}]";
            }
        } elseif (is_array($data)) {
            $data = implode(", ", $data);
        } elseif (is_bool($data)) {
            $data = $data ? 'true' : 'false';
        } elseif (is_null($data)) {
            $data = 'null';
        } elseif (is_numeric($data)) {
            $data = (string)$data;
        } else {
            if (
                is_scalar($data) ||
                (is_object($data) &&
                    method_exists($data, '__toString'))
            ) {
                $data = (string)$data;
            } else {
                $data = '[not convertible]';
            }
        }
        if (trim($data) === '' || $data === Globals::INTERNAL_EMPTY) {
            $data = '[empty]';
        }


        $data = html_entity_decode($data, ENT_QUOTES, Globals::getCharset());
        $sdata  = strlen($data) > 50 ? substr($data, 0, 50) . "..." : $data;
        $sdata  = htmlspecialchars(
            $sdata,
            ENT_QUOTES | ENT_SUBSTITUTE,
            Globals::getCharset()
        );
        return $sdata;
    }
}
