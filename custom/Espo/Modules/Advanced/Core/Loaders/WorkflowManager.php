<?php

namespace  Espo\Modules\Advanced\Core\Loaders;

use Espo\Core\Container;
use Espo\Core\Container\Loader;
use Espo\Modules\Advanced\Core\WorkflowManager as Service;

class WorkflowManager implements Loader
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function load(): Service
    {
        return new Service($this->container);
    }
}
