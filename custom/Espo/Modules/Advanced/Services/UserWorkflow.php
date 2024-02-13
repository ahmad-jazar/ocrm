<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Core\InjectableFactory;
use Espo\Core\Record\ServiceContainer;
use Espo\ORM\Entity;
use Espo\Tools\UserSecurity\Password\Service;

class UserWorkflow
{
    private InjectableFactory $injectableFactory;
    private ServiceContainer $serviceContainer;

    public function __construct(
        InjectableFactory $injectableFactory,
        ServiceContainer $serviceContainer
    ) {
        $this->injectableFactory = $injectableFactory;
        $this->serviceContainer = $serviceContainer;
    }

    public function generateAndSendPassword(?string $workflowId, Entity $entity, $data): void
    {
        if (class_exists("Espo\\Tools\\UserSecurity\\Password\\Service")) {
            /** @var Service $service */
            $service = $this->injectableFactory->create("Espo\\Tools\\UserSecurity\\Password\\Service");

            // @todo Support non-admin users.

            $service->generateAndSendNewPasswordForUser($entity->getId());

            return;
        }

        $service = $this->serviceContainer->get('User');

        if (method_exists($service, 'generateNewPasswordForUser')) {
            $service->generateNewPasswordForUser($entity->getId(), true);
        }
    }
}
