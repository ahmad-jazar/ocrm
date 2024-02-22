<?php

namespace Espo\Custom\TemplateHelpers;

use Espo\Core\Htmlizer\Helper;
use Espo\Core\Htmlizer\Helper\Data;
use Espo\Core\Htmlizer\Helper\Result;

class Mod implements Helper
{
    public function render(Data $data): Result
    {
        $value = $data->getArgumentList()[0] ?? '';
        $remainder = $data->getOption('remainder') ?? 0;

        $value = $value + 1;


        if ($value % $remainder === 0) {
            return Result::createSafeString(
                "</tr><tr>"
            );
        }

        return Result::createSafeString($data->getArgumentList() ? $value : '');
    }
}