<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Modules\Advanced\Core\Bpmn\Utils\ConditionManager;

class GatewayExclusive extends Gateway
{
    protected function processDivergent(): void
    {
        $conditionManager = $this->getConditionManager();

        $flowList = $this->getAttributeValue('flowList');

        if (!is_array($flowList)) {
            $flowList = [];
        }

        $defaultNextElementId = $this->getAttributeValue('defaultNextElementId');
        $nextElementId = null;

        foreach ($flowList as $flowData) {
            $conditionsAll = isset($flowData->conditionsAll) ? $flowData->conditionsAll : null;
            $conditionsAny = isset($flowData->conditionsAny) ? $flowData->conditionsAny : null;
            $conditionsFormula = isset($flowData->conditionsFormula) ? $flowData->conditionsFormula : null;

            $result = $conditionManager->check(
                $this->getTarget(),
                $conditionsAll,
                $conditionsAny,
                $conditionsFormula,
                $this->getVariablesForFormula()
            );

            if ($result) {
                $nextElementId = $flowData->elementId;

                break;
            }
        }

        if (!$nextElementId && $defaultNextElementId) {
            $nextElementId = $defaultNextElementId;
        }

        if ($nextElementId) {
            $this->processNextElement($nextElementId);

            return;
        }

        $this->endProcessFlow();
    }

    protected function processConvergent(): void
    {
        $this->processNextElement();
    }

    protected function getConditionManager(): ConditionManager
    {
        $conditionManager = new ConditionManager($this->getContainer());
        $conditionManager->setCreatedEntitiesData($this->getCreatedEntitiesData());

        return $conditionManager;
    }
}
