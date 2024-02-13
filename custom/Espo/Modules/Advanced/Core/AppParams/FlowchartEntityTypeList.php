<?php

namespace Espo\Modules\Advanced\Core\AppParams;

use Espo\Core\Acl;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Modules\Advanced\Entities\BpmnFlowchart;
use Espo\Modules\Advanced\Entities\BpmnProcess;
use Espo\ORM\EntityManager;

class FlowchartEntityTypeList
{
    private Acl $acl;
    private EntityManager $entityManager;
    private SelectBuilderFactory $selectBuilderFactory;

    public function __construct(
        Acl $acl,
        EntityManager $entityManager,
        SelectBuilderFactory $selectBuilderFactory
    ) {
        $this->acl = $acl;
        $this->entityManager = $entityManager;
        $this->selectBuilderFactory = $selectBuilderFactory;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        if (!$this->acl->checkScope(BpmnProcess::ENTITY_TYPE, Acl\Table::ACTION_CREATE)) {
            return [];
        }

        if (!$this->acl->checkScope(BpmnFlowchart::ENTITY_TYPE, Acl\Table::ACTION_READ)) {
            return [];
        }

        $list = [];

        $query = $this->selectBuilderFactory
            ->create()
            ->from(BpmnFlowchart::ENTITY_TYPE)
            ->withAccessControlFilter()
            ->build();

        $collection = $this->entityManager
            ->getRDBRepository(BpmnFlowchart::ENTITY_TYPE)
            ->clone($query)
            ->select(['targetType'])
            ->group('targetType')
            ->where(['isActive' => true])
            ->find();

        foreach ($collection as $item) {
            $list[] = $item->get('targetType');
        }

        return $list;
    }
}
