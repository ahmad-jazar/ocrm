<?php

namespace Espo\Custom\Tools\UpcomingSeminar\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\ORM\EntityManager;

class Get implements Action
{

    public function __construct(
        EntityManager $entityManager,
    )
    {
        $this->entityManager = $entityManager;
    }

    public function process(Request $request): Response
    {

        $data = $this->entityManager->getRepository('Seminar')->where([
            'seminarStartTime >' => date('Y-m-d H:i:s'),
            'visibility' => true
        ])->find()->toArray();
        if (empty($data)) {
            return ResponseComposer::json([
                'data' => [],
            ]);
        }
        return ResponseComposer::json([
            'total' => count($data),
            'data' => $data,
        ]);
    }
}
