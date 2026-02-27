<?php

declare(strict_types=1);

namespace FormGenerator\Config;

use Dotenv\Dotenv;

class Config
{
    /**
     * @var array<mixed> $values
     */
    private static array $values = [];
    private static bool $dotenvLoaded = false;

    /**
     * Private constructor to prevent instantiation.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        ; // This constructor is private and empty; no code is executed here.
    }

    /**
     * Load environment variables from a .env file.
     *
     * @return void
     */
    public static function loadDotenv(): void
    {
        if (self::$dotenvLoaded) {
            return;
        }

        $dir = __DIR__;
        $rootUserPath = null;

        while (true) {
            if (file_exists($dir . '/.env')) {
                $rootUserPath = $dir;
                break;
            }
            $parent = dirname($dir);
            if ($parent === $dir) {
                break;
            }
            $dir = $parent;
        }

        if ($rootUserPath !== null) {
            $dotenv = Dotenv::createImmutable($rootUserPath);
            self::$values = $dotenv->load();
        }

        self::$dotenvLoaded = true;
    }

    /**
     * Get a configuration value with fallback options.
     *
     * @param array<string> $keys
     * @param mixed $default
     * @return mixed
     */
    public static function getWithFallback(array $keys, mixed $default = null): mixed
    {
        self::loadDotenv();
        foreach ($keys as $key) {
            if (isset($_ENV[$key])) {
                return $_ENV[$key];
            }
            if (isset(self::$values[$key])) {
                return self::$values[$key];
            }
        }
        return $default;
    }


    /**
     * Get a configuration value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::loadDotenv();
        return $_ENV[$key] ?? self::$values[$key] ?? $default;
    }
}
