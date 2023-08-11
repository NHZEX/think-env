<?php

namespace Zxin\Think;

use Closure;
use Dotenv\Dotenv;
use Env\Env as EnvService;
use Zxin\Think\Event\EnvLoaded;
use InvalidArgumentException;
use think\App;
use think\Env;
use function call_user_func;

/**
 * Class Env
 * @package app\Service\Env
 *
 * 优先使用 $_SERVER https://github.com/vlucas/phpdotenv/issues/446
 */
class EnvLoader extends Env
{
    protected Dotenv $dotenv;

    protected static bool $init = false;

    protected static bool $isEnv = true;

    /** @var callable|null */
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

            $this->dotenv = Dotenv::createImmutable(dirname($file), basename($file));
            $this->data = $this->dotenv->load();

            $this->loaded();
        }
    }

    protected function loaded()
    {
        $this->dotenv->ifPresent('APP_DEBUG')->isBoolean();
        if (self::$verify !== null) {
            call_user_func(self::$verify, $this->dotenv);
        }

        App::getInstance()->event->trigger(new EnvLoaded($this));
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
     * @return array|bool|int|string|null
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
