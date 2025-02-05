<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\SearchService;

class FindContentCommand extends Command
{
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
        parent::__construct('doc:find_content');
    }

    protected function configure()
    {
        $this
            ->setDescription('Lists content belonging to the provided Content Type.')
            ->setDefinition([
                new InputArgument('contentTypeIdentifier', InputArgument::REQUIRED, 'Content Type identifier'),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentTypeIdentifier = $input->getArgument('contentTypeIdentifier');

        $query = new LocationQuery();
        $query->filter = new Criterion\ContentTypeIdentifier($contentTypeIdentifier);

        $result = $this->searchService->findContentInfo($query);

        $output->writeln('Found ' . $result->totalCount . ' items');
        foreach ($result->searchHits as $searchHit) {
            $output->writeln($searchHit->valueObject->name);
        }

        return self::SUCCESS;
    }
}
