<?php

namespace Espo\Modules\Advanced\Tools\Report\GridType;

class JointData
{
    /** @var ?array<int, object{id: string}> */
    private ?array $joinedReportDataList;
    private ?string $chartType;

    /**
     * @param ?array<int, object{id: string}> $joinedReportDataList
     * @param string|null $chartType
     */
    public function __construct(
        ?array $joinedReportDataList,
        ?string $chartType
    ) {
        $this->joinedReportDataList = $joinedReportDataList;
        $this->chartType = $chartType;
    }

    /**
     * @return array<int, object{id: string}>
     */
    public function getJoinedReportDataList(): array
    {
        return $this->joinedReportDataList;
    }

    public function getChartType(): ?string
    {
        return $this->chartType;
    }
}
