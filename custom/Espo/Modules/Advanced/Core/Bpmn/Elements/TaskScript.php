<?php

namespace Espo\Modules\Advanced\Core\Bpmn\Elements;

use Throwable;

class TaskScript extends Activity
{
    public function process(): void
    {
        $formula = $this->getAttributeValue('formula');

        if (!$formula) {
            $this->processNextElement();

            return;
        }

        if (!is_string($formula)) {
            $GLOBALS['log']->error('Process ' . $this->getProcess()->get('id') . ', formula should be string.');

            $this->setFailed();

            return;
        }

        try {
            $variables = $this->getVariablesForFormula();

            $this->getFormulaManager()->run($formula, $this->getTarget(), $variables);

            $this->getEntityManager()
                ->saveEntity($this->getTarget(), [
                    'skipWorkflow' => true,
                    'skipModifiedBy' => true,
                ]);

            $this->sanitizeVariables($variables);

            $this->getProcess()->set('variables', $variables);
            $this->getEntityManager()->saveEntity($this->getProcess(), ['silent' => true]);
        }
        catch (Throwable $e) {
            $GLOBALS['log']->error('Process ' . $this->getProcess()->get('id') . ' formula error: ' . $e->getMessage());

            $this->setFailedWithException($e);

            return;
        }

        $this->processNextElement();
    }
}
