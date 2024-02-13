<?php

namespace Espo\Modules\Advanced\Controllers;

use Espo\Core\Api\Request;
use Espo\Core\Controllers\Record;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Exceptions\NotFound;
use Espo\Core\Record\SearchParamsFetcher;
use Espo\Modules\Advanced\Tools\Report\ListType\SubReportParams;
use Espo\Modules\Advanced\Tools\ReportPanel\Service;

use stdClass;

class ReportPanel extends Record
{
    /**
     * @throws Error
     */
    public function postActionRebuild(): bool
    {
        $this->getPanelService()->rebuild();

        return true;
    }

    /**
     * @throws BadRequest
     * @throws Error
     * @throws Forbidden
     * @throws NotFound
     */
    public function getActionRunList(Request $request): stdClass
    {
        $id = $request->getQueryParam('id');

        $parentType = $request->getQueryParam('parentType');
        $parentId = $request->getQueryParam('parentId');

        if (!$id) {
            throw new BadRequest();
        }

        $searchParams = $this->injectableFactory
            ->create(SearchParamsFetcher::class)
            ->fetch($request);

        $subReportParams = null;

        if ($request->hasQueryParam('groupValue')) {
            $groupValue = $request->getQueryParam('groupValue');

            if ($groupValue === '') {
                $groupValue = null;
            }

            $groupValue2 = null;

            if ($request->hasQueryParam('groupValue2')) {
                $groupValue2 = $request->getQueryParam('groupValue2');

                if ($groupValue2 === '') {
                    $groupValue2 = null;
                }
            }

            $subReportParams = new SubReportParams(
                (int) ($request->getQueryParam('groupIndex') ?? 0),
                $groupValue,
                $request->hasQueryParam('groupValue2'),
                $groupValue2
            );
        }

        $subReportId = $request->getQueryParam('subReportId');

        $result = $subReportParams ?
            $this->getPanelService()->runSubReportList(
                $id,
                $parentType,
                $parentId,
                $searchParams,
                $subReportParams,
                $subReportId
            ) :
            $this->getPanelService()->runList($id, $parentType, $parentId, $searchParams);

        return (object) [
            'list' => $result->getCollection()->getValueMapList(),
            'total' => $result->getTotal(),
            'columns' => $result->getColumns(),
            'columnsData' => $result->getColumnsData(),
        ];
    }

    /**
     * @throws BadRequest
     * @throws Forbidden
     * @throws Error
     * @throws NotFound
     */
    public function getActionRunGrid(Request $request): stdClass
    {
        $id = $request->getQueryParam('id');
        $parentType = $request->getQueryParam('parentType');
        $parentId = $request->getQueryParam('parentId');

        if (!$id) {
            throw new BadRequest();
        }

        return $this->getPanelService()
            ->runGrid($id, $parentType, $parentId)
            ->toRaw();
    }

    private function getPanelService(): Service
    {
        return $this->injectableFactory->create(Service::class);
    }
}
