<?php

namespace Espo\Modules\Advanced\Core\Loaders;

use Espo\Core\Container\Loader;
use Espo\Core\InjectableFactory;
use Espo\Modules\Advanced\Core\SignalManager as Service;

class SignalManager implements Loader
{
    private InjectableFactory $injectableFactory;

    public function __construct(InjectableFactory $injectableFactory)
    {
        $this->injectableFactory = $injectableFactory;
    }

    public function load(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
