<?php

namespace Espo\Modules\Advanced\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Modules\Advanced\Tools\Workflow\Service;

class WorkflowManual
{
    private Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws NotFound
     */
    public function postActionRun(Request $request): bool
    {
        $data = $request->getParsedBody();

        $id = $data->id ?? null;
        $targetId = $data->targetId ?? null;

        if (!$id || !$targetId) {
            throw new BadRequest();
        }

        $this->service->runManual($id, $targetId);

        return true;
    }
}
