<?php

namespace Espo\Custom\Classes\Select\Seminar\PrimaryFilters;

use DateTime;
use Espo\Core\Select\Helpers\UserTimeZoneProvider;
use Espo\Core\Select\Primary\Filter;
use Espo\ORM\Query\SelectBuilder;

class Upcoming implements Filter
{
    public function __construct(
        private UserTimeZoneProvider $userTimeZoneProvider,
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function apply(SelectBuilder $queryBuilder): void
    {

        $timezone = $this->userTimeZoneProvider->get();
        $now = new DateTime('now', new \DateTimeZone($timezone));

        $queryBuilder->where([
//            'seminarStartTime >=' => $now->format('Y-m-d H:i:s')
            'seminarStartTime >=' => date('Y-m-d H:i:s')
        ]);
    }
}
