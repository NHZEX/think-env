<?php

namespace HZEX\Think\Event;

use Dotenv\Dotenv;
use HZEX\Think\EnvLoader;

class EnvLoaded
{
    /** @var EnvLoader */
    public $env;

    /** @var Dotenv */
    public $dotenv;

    /** @var array */
    public $envData;

    public function __construct(EnvLoader $envLoader)
    {
        $this->env = $envLoader;
        $this->dotenv = $envLoader->getDotenv();
        $this->envData = $envLoader->getRaw();
    }
}
