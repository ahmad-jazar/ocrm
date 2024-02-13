<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

abstract class Gateway extends Base
{
    public function process(): void
    {
        if ($this->isDivergent()) {
            $this->processDivergent();

            return;
        }

        if ($this->isConvergent()) {
            $this->processConvergent();

            return;
        }

        $this->setFailed();
    }

    abstract protected function processDivergent(): void;

    abstract protected function processConvergent(): void;

    protected function isDivergent(): bool
    {
        $nextElementIdList = $this->getAttributeValue('nextElementIdList') ?? [];

        return !$this->isConvergent() && count($nextElementIdList);
    }

    protected function isConvergent(): bool
    {
        $previousElementIdList = $this->getAttributeValue('previousElementIdList') ?? [];

        return is_array($previousElementIdList) && count($previousElementIdList) > 1;
    }

    private function checkElementsBelongSingleFlowRecursive(
        $divergentElementId,
        $forkElementId,
        $currentElementId,
        bool &$result,
        &$metElementIdList = null
    ): void {

        if ($divergentElementId === $currentElementId) {
            return;
        }

        if ($forkElementId === $currentElementId) {
            $result = true;

            return;
        }

        if (!$metElementIdList) {
            $metElementIdList = [];
        }

        $flowchartElementsDataHash = $this->getProcess()->get('flowchartElementsDataHash');

        $elementData = $flowchartElementsDataHash->$currentElementId;

        if (!isset($elementData->previousElementIdList)) {
            return;
        }

        foreach ($elementData->previousElementIdList as $elementId) {
            if (in_array($elementId, $metElementIdList)) {
                continue;
            }

            $this->checkElementsBelongSingleFlowRecursive(
                $divergentElementId,
                $forkElementId,
                $elementId,
                $result,
                $metElementIdList
            );
        }
    }

    protected function checkElementsBelongSingleFlow(
        $divergentElementId,
        $forkElementId,
        $elementId
    ): bool {

        $result = false;

        $this->checkElementsBelongSingleFlowRecursive($divergentElementId, $forkElementId, $elementId, $result);

        return $result;
    }
}
