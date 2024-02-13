<?php

namespace Espo\Modules\Advanced\Classes\AppParams;

use Espo\Entities\User;
use Espo\Modules\Advanced\Entities\Workflow;
use Espo\ORM\Collection;
use Espo\ORM\EntityManager;
use stdClass;

class ManualWorkflows
{
    private EntityManager $entityManager;
    private User $user;

    public function __construct(
        EntityManager $entityManager,
        User $user
    ) {
        $this->entityManager = $entityManager;
        $this->user = $user;
    }

    /**
     * @return stdClass
     */
    public function get()
    {
        $data = (object) [];

        $builder = $this->entityManager
            ->getRDBRepository(Workflow::ENTITY_TYPE)
            ->where([
                'type' => Workflow::TYPE_MANUAL,
                'isActive' => true,
            ]);

        if (!$this->user->isAdmin()) {
            $builder
                ->distinct()
                ->join('manualTeams')
                ->where(['manualTeams.id' => $this->user->getTeamIdList()]);

            $builder->where(['manualAccessRequired!=' => 'admin']);
        }

        /** @var Collection<Workflow> $workflows */
        $workflows = $builder->find();

        foreach ($workflows as $workflow) {
            $entityType = $workflow->getTargetEntityType();

            if (!property_exists($data, $entityType)) {
                $data->$entityType = [];
            }

            $item = (object) [
                'id' => $workflow->getId(),
                'label' => $workflow->get('manualLabel'),
                'accessRequired' => $workflow->get('manualAccessRequired'),
                'elementType' => $workflow->get('manualElementType'),
                'dynamicLogic' => $workflow->get('manualDynamicLogic'),
            ];

            $data->$entityType[] = $item;
        }

        return $data;
    }
}
