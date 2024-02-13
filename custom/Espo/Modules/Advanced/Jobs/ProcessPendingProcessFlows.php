<?php

namespace Espo\Modules\Advanced\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Core\Utils\Log;
use Espo\Modules\Advanced\Core\Bpmn\BpmnManager;

use Exception;

class ProcessPendingProcessFlows implements JobDataLess
{
    private BpmnManager $bpmnManager;
    private Log $log;

    public function __construct(
        BpmnManager $bpmnManager,
        Log $log
    ) {
        $this->bpmnManager = $bpmnManager;
        $this->log = $log;
    }

    public function run(): void
    {
        try {
            $this->bpmnManager->processPendingFlows();
        }
        catch (Exception $e) {
            $this->log->error("ProcessPendingProcessFlows: {$e->getCode()}, {$e->getMessage()}");
        }
    }
}
