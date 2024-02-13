<?php

namespace Espo\Modules\Advanced\Jobs;

use Espo\Core\Job\JobDataLess;
use Espo\Core\Utils\Log;
use Espo\Modules\Advanced\Tools\Report\SendingService;
use Exception;

class ScheduleReportSending implements JobDataLess
{
    private SendingService $sendingService;
    private Log $log;

    public function __construct(
        SendingService $sendingService,
        Log $log
    ) {
        $this->sendingService = $sendingService;
        $this->log = $log;
    }

    public function run(): void
    {
        try {
            $this->sendingService->scheduleEmailSending();
        }
        catch (Exception $e) {
            $this->log->error('ScheduleReportSending: ' . $e->getMessage());
        }
    }
}
