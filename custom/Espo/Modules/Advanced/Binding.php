<?php

namespace Espo\Modules\Advanced;

use Espo\Core\Binding\Binder;
use Espo\Core\Binding\BindingProcessor;
use Espo\Modules\Advanced\Core\SignalManager;
use Espo\Modules\Advanced\Core\Workflow\Helper as WorkflowHelper;
use Espo\Modules\Advanced\Core\WorkflowManager;

class Binding implements BindingProcessor
{
    public function process(Binder $binder): void
    {
        $binder->bindService('workflowManager', WorkflowManager::class);
        $binder->bindService('workflowHelper', WorkflowHelper::class);
        $binder->bindService('signalManager', SignalManager::class);
    }
}
