<?php

namespace Espo\Modules\Advanced\Tools\Report;

use Espo\Core\Exceptions\Error\Body;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\Metadata;

class FormulaChecker
{

    private array $allowedFunctionList = [
        'ifThen',
        'ifThenElse',
        'env\\userAttribute',
        'record\\attribute',
    ];

    private array $allowedNamespaceList = [
        'datetime',
        'number',
        'string',
    ];

    private Metadata $metadata;

    public function __construct(
        Metadata $metadata
    ) {
        $this->metadata = $metadata;
    }


    /**
     * Check a formula script for a complex expression.
     *
     * @throws Forbidden
     */
    public function check(string $script): void
    {
        $script = str_replace(["\n", "\r", "\t", ' '], '', $script);

        $script = str_replace(';', ' ', $script);

        preg_match_all('/[a-zA-Z1-9\\\\]*\(/', $script, $matches);

        if (!$matches) {
            return;
        }

        $allowedFunctionList = array_merge(
            $this->allowedFunctionList,
            $this->metadata->get('app.advancedReport.allowedFilterFormulaFunctionList', [])
        );

        foreach ($matches[0] as $part) {
            $part = substr($part, 0, -1);

            if (in_array($part, $allowedFunctionList)) {
                continue;
            }

            foreach ($this->allowedNamespaceList as $namespace) {
                if (strpos($part, $namespace . '\\') === 0) {
                    continue 2;
                }
            }

            throw Forbidden::createWithBody(
                "Not allowed formula in filter.",
                Body::create()
                    ->withMessageTranslation('notAllowedFormulaInFilter', 'Report')
                    ->encode()
            );
        }
    }
}
