<?php

namespace App\Tab\Dashboard\Everyone;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Ibexa\Contracts\AdminUi\Tab\AbstractTab;
use Ibexa\Contracts\AdminUi\Tab\OrderedTabInterface;
use Ibexa\AdminUi\Tab\Dashboard\PagerContentToDataMapper;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Pagerfanta\Pagerfanta;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class EveryoneArticleTab extends AbstractTab implements OrderedTabInterface
{
    protected $pagerContentToDataMapper;

    protected $searchService;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        PagerContentToDataMapper $pagerContentToDataMapper,
        SearchService $searchService
    ) {
        parent::__construct($twig, $translator);

        $this->pagerContentToDataMapper = $pagerContentToDataMapper;
        $this->searchService = $searchService;
    }

    public function getIdentifier(): string
    {
        return 'everyone-article';
    }

    public function getName(): string
    {
        return 'Articles';
    }

    public function getOrder(): int
    {
        return 300;
    }

    public function renderView(array $parameters): string
    {
        $page = 1;
        $limit = 10;

        $query = new LocationQuery();

        $query->sortClauses = [new SortClause\DateModified(LocationQuery::SORT_DESC)];
        $query->query = new Criterion\LogicalAnd([
            new Criterion\ContentTypeIdentifier('article'),
        ]);

        $pager = new Pagerfanta(
            new ContentSearchAdapter($query,
                $this->searchService
            )
        );
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        return $this->twig->render('@ibexadesign/ui/dashboard/tab/all_content.html.twig', [
            'data' => $this->pagerContentToDataMapper->map($pager),
        ]);
    }
}
