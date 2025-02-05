<?php

declare(strict_types=1);

namespace App\FieldType\HelloWorld\Comparison;

use Ibexa\VersionComparison\ComparisonValue\StringComparisonValue;
use Ibexa\Contracts\VersionComparison\FieldType\Comparable as ComparableInterface;
use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\VersionComparison\FieldType\FieldTypeComparisonValue;

final class Comparable implements ComparableInterface
{
    public function getDataToCompare(SPIValue $value): FieldTypeComparisonValue
    {
        return new Value([
            'name' => new StringComparisonValue([
                'value' => $value->getName(),
            ]),
        ]);
    }
}
