<?php

namespace HZEX\Think;

use Closure;
use Dotenv\Dotenv;
use Env\Env as EnvService;
use InvalidArgumentException;
use think\Env;

/**
 * Class Env
 * @package app\Service\Env
 *
 * 优先使用 $_SERVER https://github.com/vlucas/phpdotenv/issues/446
 */
class EnvLoader extends Env
{
    /** @var Dotenv */
    protected $dotenv;

    protected static $init = false;

    protected static $isEnv = true;

    /** @var Closure|null */
    protected static $verify = null;

    /**
     * @param Closure $closure
     */
    public static function registerVerify(Closure $closure)
    {
        self::$verify = $closure;
    }

    /**
     * @param string $file
     */
    public function load(string $file): void
    {
        if (!self::$init) {
            self::$init = true;
            self::$isEnv = isset($_ENV) && !empty($_ENV);

            EnvService::$options ^= EnvService::STRIP_QUOTES;
            if (self::$isEnv) {
                EnvService::$options |= EnvService::USE_ENV_ARRAY;
            } else {
                EnvService::$options |= EnvService::USE_SERVER_ARRAY;
            }

            $dotenv = Dotenv::createImmutable(dirname($file), basename($file));
            $this->data = $dotenv->load();
            $dotenv->ifPresent('APP_DEBUG')->isBoolean();
            if (self::$verify) {
                (self::$verify)($dotenv);
            }
        }
    }

    /**
     * @return array
     */
    public function getRaw(): array
    {
        return $this->data;
    }

    /**
     * @return Dotenv
     */
    public function getDotenv(): Dotenv
    {
        return $this->dotenv;
    }

    /**
     * @param string|null $name
     * @param mixed|null  $default
     * @return array|bool|\Env\string|int|mixed|string|null
     */
    public function get(string $name = null, $default = null)
    {
        if (is_null($name)) {
            return $this->data;
        }

        $name = strtoupper(str_replace('.', '_', $name));

        return EnvService::get($name) ?? $default;
    }

    /**
     * @param array|string $env
     * @param mixed|null   $value
     */
    public function set($env, $value = null): void
    {
        if (!is_string($env)) {
            throw new InvalidArgumentException('env name only support string');
        }

        $name = strtoupper(str_replace('.', '_', $env));

        if (self::$isEnv) {
            $_ENV[$name] = $value;
        } else {
            $_SERVER[$name] = $value;
        }
    }
}
