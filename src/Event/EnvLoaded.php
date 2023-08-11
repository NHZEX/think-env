<?php

namespace Zxin\Think\Event;

use Dotenv\Dotenv;
use Zxin\Think\EnvLoader;

class EnvLoaded
{
    public EnvLoader $env;

    public Dotenv $dotenv;

    public array $envData;

    public function __construct(EnvLoader $envLoader)
    {
        $this->env = $envLoader;
        $this->dotenv = $envLoader->getDotenv();
        $this->envData = $envLoader->getRaw();
    }
}
