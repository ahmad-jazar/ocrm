<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Espo\Core\Exceptions\Error;

use Espo\Modules\Advanced\Entities\BpmnFlowNode;
use Exception;
use DateTime;

class EventIntermediateTimerCatch extends Event
{
    protected $pendingStatus = BpmnFlowNode::STATUS_PENDING;

    public function process(): void
    {
        $timerBase = $this->getAttributeValue('timerBase');

        if (!$timerBase || $timerBase === 'moment') {
            $dt = new DateTime();

            $this->shiftDateTime($dt);
        }
        else if ($timerBase === 'formula') {
            $timerFormula = $this->getAttributeValue('timerFormula');

            $formulaManager = $this->getFormulaManager();

            if (!$timerFormula) {
                $this->setFailed();

                throw new Error('Bpmn Flow: EventIntermediateTimer error.');
            }

            $value = $formulaManager->run($timerFormula, $this->getTarget(), $this->getVariablesForFormula());

            if (!$value || !is_string($value)) {
                $this->setFailed();

                throw new Error();
            }

            try {
                $dt = new DateTime($value);
            }
            catch (Exception $e) {
                $this->setFailed();

                throw new Error('Bpmn Flow: EventIntermediateTimer error.');
            }
        }
        else if (strpos($timerBase, 'field:') === 0) {
            $field = substr($timerBase, 6);
            $entity = $this->getTarget();

            if (strpos($field, '.') > 0) {
                [$link, $field] = explode('.', $field);

                $target = $this->getTarget();

                $entity = method_exists($this->getEntityManager(), 'getRDBRepository') ?
                    $this->getEntityManager()
                        ->getRDBRepository($target->getEntityType())
                        ->getRelation($target, $link)
                        ->findOne() :
                    $target->get($link);

                if (!$entity) {
                    $this->setFailed();

                    throw new Error("Bpmn Flow: EventIntermediateTimer. Related entity doesn't exist.");
                }
            }

            $value = $entity->get($field);

            if (!$value || !is_string($value)) {
                $this->setFailed();

                throw new Error('Bpmn Flow: EventIntermediateTimer.');
            }

            try {
                $dt = new DateTime($value);
            }
            catch (Exception $e) {
                $this->setFailed();

                throw new Error('Bpmn Flow: EventIntermediateTimer error.');
            }

            $this->shiftDateTime($dt);
        }
        else {
            $this->setFailed();

            throw new Error('Bpmn Flow: EventIntermediateTimer error.');
        }

        $flowNode = $this->getFlowNode();

        $flowNode->set([
            'status' => $this->pendingStatus,
            'proceedAt' => $dt->format('Y-m-d H:i:s')
        ]);

        $this->getEntityManager()->saveEntity($flowNode);
    }

    protected function shiftDateTime(DateTime $dt)
    {
        $timerShiftOperator = $this->getAttributeValue('timerShiftOperator');
        $timerShift = $this->getAttributeValue('timerShift');
        $timerShiftUnits = $this->getAttributeValue('timerShiftUnits');

        if (!in_array($timerShiftUnits, ['minutes', 'hours', 'days', 'months', 'seconds'])) {
            $flowNode = $this->getFlowNode();

            $this->setFailed();

            throw new Error("Bpmn Flow: Bad shift in ". $flowNode->get('elementType') . " " .
                $flowNode->get('elementId') . " in flowchart " . $flowNode->get('flowchartId') . ".");
        }

        if ($timerShift) {
            $modifyString = strval($timerShift) . ' ' . $timerShiftUnits;

            if ($timerShiftOperator === 'minus') {
                $modifyString = '-' . $modifyString;
            }

            try {
                $dt->modify($modifyString);
            }
            catch (Exception $e) {
                $this->setFailed();

                throw new Error('Bpmn Flow: EventIntermediateTimer error.');
            }
        }
    }

    public function proceedPending(): void
    {
        $this->getFlowNode()->set('status', BpmnFlowNode::STATUS_IN_PROCESS);
        $this->saveFlowNode();

        $this->rejectConcurrentPendingFlows();
        $this->processNextElement();
    }
}
