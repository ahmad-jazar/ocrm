<?php

namespace Espo\Modules\Advanced\Tools\Report\GridType;

use Espo\Core\Select\SelectBuilderFactory;
use Espo\Core\Select\Where\Item as WhereItem;
use Espo\Entities\User;
use Espo\Modules\Advanced\Tools\Report\SelectHelper;
use Espo\ORM\Query\Part\Condition as Cond;
use Espo\ORM\Query\Part\Expression as Expr;
use Espo\ORM\Query\Select;
use Espo\ORM\Query\SelectBuilder;

class QueryPreparator
{
    private const WHERE_TYPE_AND = 'and';

    private SelectHelper $selectHelper;
    private SelectBuilderFactory $selectBuilderFactory;
    private Helper $helper;

    public function __construct(
        SelectHelper $selectHelper,
        SelectBuilderFactory $selectBuilderFactory,
        Helper $helper
    ) {
        $this->selectHelper = $selectHelper;
        $this->selectBuilderFactory = $selectBuilderFactory;
        $this->helper = $helper;
    }

    public function prepare(Data $data, ?WhereItem $where, ?User $user): Select
    {
        [$whereItem, $havingItem] = $this->obtainWhereAndHavingItems($data);

        $queryBuilder = SelectBuilder::create()
            ->from($data->getEntityType(), lcfirst($data->getEntityType()));

        $this->selectHelper->handleGroupBy($data->getGroupBy(), $queryBuilder);
        $this->selectHelper->handleColumns($data->getAggregatedColumns(), $queryBuilder);
        $this->selectHelper->handleOrderBy($data->getOrderBy(), $queryBuilder);
        $this->selectHelper->handleFiltersHaving($havingItem, $queryBuilder, true);

        $preFilterQuery = $queryBuilder->build();

        $queryBuilder = $this->cloneWithAccessControlAndWhere($data, $where, $user, $preFilterQuery);

        $this->selectHelper->handleFiltersWhere($whereItem, $queryBuilder, true);
        $this->handleAdditional($queryBuilder);

        if (!$this->useSubQuery($queryBuilder)) {
            return $queryBuilder->build();
        }

        $subQuery = $queryBuilder
            ->select(['id'])
            ->group([])
            ->order([])
            ->having([])
            ->build();

        return SelectBuilder::create()
            ->clone($preFilterQuery)
            ->where(
                Cond::in(Expr::column('id'), $subQuery)
            )
            ->build();
    }

    private function cloneWithAccessControlAndWhere(
        Data $data,
        ?WhereItem $where,
        ?User $user,
        Select $preFilterQuery
    ): SelectBuilder  {

        $selectBuilder = $this->selectBuilderFactory
            ->create()
            ->clone($preFilterQuery);

        if ($user) {
            $selectBuilder
                ->forUser($user)
                ->withWherePermissionCheck();
        }

        if ($user && $data->applyAcl()) {
            $selectBuilder->withAccessControlFilter();
        }

        $selectBuilder->buildQueryBuilder();

        if ($where) {
            $selectBuilder->withWhere($where);
        }

        $queryBuilder = $selectBuilder->buildQueryBuilder();

        if ($where) {
            // Supposed to be already applied by the scanner.
            $this->selectHelper->applyLeftJoinsFromWhere($where, $queryBuilder);
        }

        return $queryBuilder;
    }

    /**
     * @param Data $data
     * @return array{0: WhereItem, 1: WhereItem}
     */
    private function obtainWhereAndHavingItems(Data $data): array
    {
        return $data->getFiltersWhere() ?
            $this->selectHelper->splitHavingItem($data->getFiltersWhere()) :
            [
                WhereItem::createBuilder()
                    ->setType(self::WHERE_TYPE_AND)
                    ->setItemList([])
                    ->build(),
                WhereItem::createBuilder()
                    ->setType(self::WHERE_TYPE_AND)
                    ->setItemList([])
                    ->build()
            ];
    }

    private function useSubQuery(SelectBuilder $queryBuilder): bool
    {
        $isDistinct = $queryBuilder->build()->isDistinct();

        if (!$isDistinct) {
            return false;
        }

        foreach ($queryBuilder->build()->getSelect() as $selectItem) {
            $itemExpr = $selectItem->getExpression()->getValue();

            if (
                strpos($itemExpr, 'SUM:') === 0 ||
                strpos($itemExpr, 'AVG:') === 0
            ) {
                return true;
            }
        }

        return false;
    }

    private function handleAdditional(SelectBuilder $queryBuilder): void
    {
        foreach ($queryBuilder->build()->getGroup() as $groupBy) {
            $groupColumn = $groupBy->getValue();

            if ($this->helper->isColumnDateFunction($groupColumn)) {
                $queryBuilder->where(["$groupColumn!=" => null]);
            }
        }
    }
}
