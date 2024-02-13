<?php

namespace Espo\Modules\Advanced\Tools\Report\ListType;

use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Select\SearchParams;
use Espo\Core\Select\SelectBuilderFactory;
use Espo\Entities\User;
use Espo\Modules\Advanced\Tools\Report\SelectHelper;
use Espo\ORM\Query\Part\Selection;
use Espo\ORM\Query\SelectBuilder;

class QueryPreparator
{
    private SelectHelper $selectHelper;
    private SelectBuilderFactory $selectBuilderFactory;

    public function __construct(
        SelectHelper $selectHelper,
        SelectBuilderFactory $selectBuilderFactory
    ) {
        $this->selectHelper = $selectHelper;
        $this->selectBuilderFactory = $selectBuilderFactory;
    }

    /**
     * Complex expression check is not applied for search parameters as it's supposed
     * to be checked the by runtime filter checker.
     *
     * @throws Forbidden
     * @throws Error
     */
    public function prepare(
        Data $data,
        ?SearchParams $searchParams = null,
        ?User $user = null
    ): SelectBuilder {

        $searchParams = $searchParams ?? SearchParams::create();

        $orderBy = $searchParams->getOrderBy();
        $order = $searchParams->getOrder();

        if ($orderBy && strpos($orderBy, '_') !== false) {
            $searchParams = $searchParams
                ->withOrderBy(null);
        }

        $selectBuilder = $this->selectBuilderFactory
            ->create()
            ->from($data->getEntityType())
            ->withSearchParams($searchParams->withSelect(['id']));

        if ($user) {
            $selectBuilder
                ->forUser($user)
                ->withWherePermissionCheck()
                ->withAccessControlFilter();
        }

        // Applies access control check.
        $intermediateQuery = $selectBuilder->build();

        $selectBuilder = $this->selectBuilderFactory
            ->create()
            ->from($data->getEntityType())
            ->withSearchParams($searchParams);

        if ($user) {
            $selectBuilder
                ->forUser($user)
                ->withAccessControlFilter();
        }

        $queryBuilder = $selectBuilder
            ->buildQueryBuilder()
            ->from($data->getEntityType(), lcfirst($data->getEntityType()));

        if ($data->getColumns() !== []) {
            // Add columns applied from order-by.

            $queryBuilder->select(
                // Prevent issue in ORM (as of v7.5).
                array_map(function (Selection $selection) {
                    return !$selection->getAlias() ?
                        $selection->getExpression() :
                        $selection;
                }, $intermediateQuery->getSelect())
            );

            $this->selectHelper->handleColumns($data->getColumns(), $queryBuilder);
        }

        if ($data->getFiltersWhere()) {
            [$whereItem, $havingItem] = $this->selectHelper->splitHavingItem($data->getFiltersWhere());

            $this->selectHelper->handleFiltersWhere($whereItem, $queryBuilder);
            $this->selectHelper->handleFiltersHaving($havingItem, $queryBuilder);
        }

        if ($orderBy) {
            $this->selectHelper->handleOrderByForList($orderBy, $order, $queryBuilder);
        }

        if ($searchParams->getWhere()) {
            $this->selectHelper->applyDistinctFromWhere($searchParams->getWhere(), $queryBuilder);
        }

        return $queryBuilder;
    }
}
