<?php

namespace Espo\Custom\Core\Formula\Functions\StringGroup;

use Espo\Core\Formula\{
    Functions\BaseFunction,
    ArgumentList,
};
use NumberFormatter;

class Tafqeet extends BaseFunction
{
    public function process(ArgumentList $args)
    {
        $args = $this->evaluate($args);
        $number = $args[0];
        $language = $args[1] ?? "en";

        $numberFormatter = new NumberFormatter($language, NumberFormatter::SPELLOUT);

        return $numberFormatter->format($number);
    }
}
