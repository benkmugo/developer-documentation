<?php

declare(strict_types=1);

namespace App\Query\SortClause\Solr;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

final class ScoreSortClause extends SortClause
{
    public function __construct(string $sortDirection = Query::SORT_ASC)
    {
        parent::__construct('_score', $sortDirection);
    }
}