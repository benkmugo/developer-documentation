<?php

namespace App\Controller;

use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Bundle\Core\Controller;
use Ibexa\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class PaginationController extends Controller
{
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function showContentAction(Request $request, $locationId)
    {
        $query = new LocationQuery();
        $query->filter = new Criterion\ParentLocationId($locationId);

        $pager = new Pagerfanta(
            new ContentSearchAdapter($query, $this->searchService)
        );
        $pager->setMaxPerPage(3);
        $pager->setCurrentPage($request->get('page', 1));

        $pager->getMaxScore();
        $pager->getTime();

        return $this->render('custom_pagination.html.twig', [
                'totalItemCount' => $pager->getNbResults(),
                'pagerItems' => $pager,
            ]
        );
    }
}
