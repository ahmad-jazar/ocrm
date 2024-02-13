<?php

namespace Espo\Modules\Advanced\Core\Cleanup;

//use Espo\Core\Cleanup\Cleanup;
use Espo\Core\Utils\Config;
use Espo\ORM\EntityManager;
use DateTime;

class WorkflowLog /*implements Cleanup*/
{
    private EntityManager $entityManager;
    private Config $config;

    public function __construct(
        EntityManager $entityManager,
        Config $config
    ) {
        $this->entityManager= $entityManager;
        $this->config = $config;
    }

    public function process(): void
    {
        $period = '-' . $this->config->get('cleanupWorkflowLogPeriod', '2 months');
        $datetime = new DateTime();
        $datetime->modify($period);

        $deleteQuery = $this->entityManager
            ->getQueryBuilder()
            ->delete()
            ->from('WorkflowLogRecord')
            ->where(['createdAt<' => $datetime->format('Y-m-d H:i:s')])
            ->build();

        $this->entityManager
            ->getQueryExecutor()
            ->execute($deleteQuery);
    }
}
