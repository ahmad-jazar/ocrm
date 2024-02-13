<?php

namespace Espo\Modules\Advanced\Services;

use Espo\Core\Di\CryptAware;
use Espo\Core\Di\CryptSetter;
use Espo\Core\Di\DefaultLanguageAware;
use Espo\Core\Di\DefaultLanguageSetter;
use Espo\Core\Di\EmailSenderAware;
use Espo\Core\Di\EmailSenderSetter;
use Espo\Services\Record;

class Workflow extends Record implements

    EmailSenderAware,
    CryptAware,
    DefaultLanguageAware
{
    use EmailSenderSetter;
    use CryptSetter;
    use DefaultLanguageSetter;

    protected $forceSelectAllAttributes = true;
    protected $readOnlyAttributeList = ['isInternal'];
}
