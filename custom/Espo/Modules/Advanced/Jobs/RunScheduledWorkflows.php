<?php

namespace Espo\Modules\Advanced\Jobs;

use Cron\CronExpression;

use Espo\Core\Job\JobDataLess;
use Espo\Core\Job\QueueName;
use Espo\Core\Utils\Config;
use Espo\Core\Utils\DateTime as DateTimeUtil;
use Espo\Core\Utils\Log;
use Espo\Entities\Job;
use Espo\Modules\Advanced\Entities\Workflow;
use Espo\Modules\Advanced\Tools\Workflow\Jobs\RunScheduledWorkflow as RunScheduledWorkflowJob;
use Espo\ORM\EntityManager;

use Exception;
use DateTime;
use DateTimeZone;

class RunScheduledWorkflows implements JobDataLess
{
    private EntityManager $entityManager;
    private Config $config;
    private Log $log;

    public function __construct(
        EntityManager $entityManager,
        Config $config,
        Log $log
    ) {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->log = $log;
    }

    public function run(): void
    {
        $collection = $this->entityManager
            ->getRDBRepository(Workflow::ENTITY_TYPE)
            ->where([
                'type' => 'scheduled',
                'isActive' => true,
            ])
            ->find();

        $defaultTimeZone = $this->config->get('timeZone');

        foreach ($collection as $entity) {
            $cronExpression = CronExpression::factory($entity->get('scheduling'));

            $timeZone = $entity->get('schedulingApplyTimezone') ?
                $defaultTimeZone : null;

            try {
                $executionTime = $cronExpression
                    ->getNextRunDate('now', 0, true, $timeZone)
                    ->setTimezone(new DateTimeZone('UTC'))
                    ->format(DateTimeUtil::SYSTEM_DATE_TIME_FORMAT);
            }
            catch (Exception $e) {
                $this->log->error(
                    'RunScheduledWorkflows: Workflow ' . $entity->getId() . ': '.
                    'Impossible scheduling expression '. $entity->get('scheduling') . '.');

                continue;
            }

            if ($entity->get('lastRun') === $executionTime) {
                continue;
            }

            if ($this->jobExists($executionTime, $entity->getId())) {
                return;
            }

            $jobData = ['workflowId' => $entity->getId()];

            $this->createJob($jobData, $executionTime, $entity->getId());

            $entity->set('lastRun', $executionTime);

            $this->entityManager->saveEntity($entity, ['silent' => true]);
        }
    }

    private function createJob(array $jobData, string $executionTime, string $workflowId): void
    {
        $job = $this->entityManager->getNewEntity(Job::ENTITY_TYPE);

        $job->set([
            'name' => RunScheduledWorkflowJob::class,
            'className' => RunScheduledWorkflowJob::class,
            'data' => $jobData,
            'executeTime' => $executionTime,
            'targetId' => $workflowId,
            'targetType' => Workflow::ENTITY_TYPE,
            'queue' => QueueName::Q1,
        ]);

        $this->entityManager->saveEntity($job);
    }

    private function jobExists(string $time, string $workflowId): bool
    {
        $timeWithoutSeconds = (new DateTime($time))->format('Y-m-d H:i:');

        $found = $this->entityManager
            ->getRDBRepository(Job::ENTITY_TYPE)
            ->select(['id'])
            ->where([
                'OR' => [
                    'className' => RunScheduledWorkflowJob::class,
                    'methodName' => 'runScheduledWorkflow', // @todo Remove.
                ],
                'executeTime*' => $timeWithoutSeconds . '%',
                'targetId' => $workflowId,
                'targetType' => Workflow::ENTITY_TYPE,
            ])
            ->findOne();

        if ($found) {
            return true;
        }

        return false;
    }
}
