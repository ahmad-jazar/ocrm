<?php

namespace Espo\Modules\Advanced\Tools\Report;

use Espo\Core\Acl;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\ServiceContainer;
use Espo\Core\Utils\Metadata;
use Espo\Modules\Advanced\Entities\Report;
use Espo\Modules\Crm\Entities\TargetList;
use Espo\Modules\Crm\Services\TargetList as TargetListService;
use Espo\ORM\EntityManager;

use RuntimeException;

class TargetListSyncService
{
    private EntityManager $entityManager;
    private Acl $acl;
    private Metadata $metadata;
    private ServiceContainer $serviceContainer;
    private Service $service;

    public function __construct(
        EntityManager $entityManager,
        Acl $acl,
        Metadata $metadata,
        ServiceContainer $serviceContainer,
        Service $service
    ) {
        $this->entityManager = $entityManager;
        $this->acl = $acl;
        $this->metadata = $metadata;
        $this->serviceContainer = $serviceContainer;
        $this->service = $service;
    }

    /**
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function syncTargetListWithReportsById(string $targetListId): void
    {
        /** @var ?TargetList $targetList */
        $targetList = $this->entityManager->getEntity(TargetList::ENTITY_TYPE, $targetListId);

        if (!$targetList) {
            throw new NotFound();
        }

        if (!$targetList->get('syncWithReportsEnabled')) {
            throw new Error("Sync with reports not enabled for target list $targetListId.");
        }

        $this->syncTargetListWithReports($targetList);
    }

    /**
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function syncTargetListWithReports(TargetList $targetList): void
    {
        if (!$this->acl->checkEntityEdit($targetList)) {
            throw new Forbidden();
        }

        $targetListService = $this->serviceContainer->get(TargetList::ENTITY_TYPE);

        if (!$targetListService instanceof TargetListService) {
            throw new RuntimeException();
        }

        if ($targetList->get('syncWithReportsUnlink')) {
            $linkList = $this->metadata->get(['scopes', 'TargetList', 'targetLinkList']) ??
                ['contacts', 'leads', 'accounts', 'users'];

            foreach ($linkList as $link) {
                $targetListService->unlinkAll($targetList->getId(), $link);
            }
        }

        $reportList = $this->entityManager
            ->getRDBRepository(TargetList::ENTITY_TYPE)
            ->getRelation($targetList, 'syncWithReports')
            ->find();

        foreach ($reportList as $report) {
            $this->populateTargetList($report->getId(), $targetList->getId());
        }
    }

    /**
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function populateTargetList(string $id, string $targetListId): void
    {
        /** @var ?Report $report */
        $report = $this->entityManager->getEntityById(Report::ENTITY_TYPE, $id);

        if (!$report) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityRead($report)) {
            throw new Forbidden();
        }

        $targetList = $this->entityManager->getEntity(TargetList::ENTITY_TYPE, $targetListId);

        if (!$targetList) {
            throw new NotFound();
        }

        if (!$this->acl->checkEntityEdit($targetList)) {
            throw new Forbidden();
        }

        if ($report->getType() !== Report::TYPE_LIST) {
            throw new Error("Report is not of 'List' type.");
        }

        $entityType = $report->getTargetEntityType();

        $linkList = $this->metadata->get(['scopes', 'TargetList', 'targetLinkList']) ??
            ['contacts', 'leads', 'accounts', 'users'];

        $link = null;

        foreach ($linkList as $itemLink) {
            if (
                $this->metadata->get(['entityDefs', 'TargetList', 'links', $itemLink, 'entity']) === $entityType
            ) {
                $link = $itemLink;

                break;
            }
        }

        if (!$link) {
            throw new Error("Not supported entity type '$entityType' for target list sync.");
        }

        $query = $this->service
            ->prepareSelectBuilder($report)
            ->build();

        $this->entityManager
            ->getRDBRepository(TargetList::ENTITY_TYPE)
            ->getRelation($targetList, $link)
            ->massRelate($query);
    }
}
