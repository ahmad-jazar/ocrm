<?php

namespace Espo\Modules\Advanced\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Core\Utils\Log;
use Espo\Modules\Advanced\Tools\Report\TargetListSyncService;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\ORM\EntityManager;

use Exception;

class ReportTargetListSync implements JobDataLess
{
    private TargetListSyncService $service;
    private EntityManager $entityManager;
    private Log $log;

    public function __construct(
        TargetListSyncService $service,
        EntityManager $entityManager,
        Log $log
    ) {
        $this->service = $service;
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    public function run(): void
    {
        $targetListList = $this->entityManager
            ->getRDBRepository(TargetList::ENTITY_TYPE)
            ->where(['syncWithReportsEnabled' => true])
            ->find();

        foreach ($targetListList as $targetList) {
            try {
                $this->service->syncTargetListWithReports($targetList);
            }
            catch (Exception $e) {
                $this->log->error("ReportTargetListSync: {$e->getCode()}, {$e->getMessage()}");
            }
        }
    }
}
