<?php

namespace Espo\Modules\Advanced\Tools\Workflow\Jobs;

use Espo\Core\Job\Job;
use Espo\Core\Job\Job\Data;
use Espo\Modules\Advanced\Tools\Workflow\SendEmailService;

class SendEmail implements Job
{
    private SendEmailService $service;

    public function __construct(SendEmailService $service)
    {
        $this->service = $service;
    }

    public function run(Data $data): void
    {
        $this->service->send($data->getRaw());
    }
}
