<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Ibexa\Contracts\Core\Repository\Values\URL\URLQuery;
use Ibexa\Contracts\Core\Repository\Values\URL\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\URL\Query\SortClause;
use Ibexa\Contracts\Core\Repository\URLService;
use Ibexa\Contracts\Core\Repository\UserService;
use Ibexa\Contracts\Core\Repository\PermissionResolver;

class FindUrlCommand extends Command
{
    private $urlService;

    private $userService;

    private $permissionResolver;

    public function __construct(URLService $URLService, UserService $userService, PermissionResolver $permissionResolver)
    {
        $this->urlService = $URLService;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
        parent::__construct('doc:find_url');
    }

    protected function configure()
    {
        $this
            ->setDescription('Finds all valid URLs in the provided Section.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->userService->loadUserByLogin('admin');
        $this->permissionResolver->setCurrentUserReference($user);

        $query = new URLQuery();

        $query->filter = new Criterion\LogicalAnd(
            [
                new Criterion\SectionIdentifier(['standard']),
                new Criterion\Validity(true),
            ]
        );
        $query->sortClauses = [
            new SortClause\URL(SortClause::SORT_DESC)
        ];
        $query->offset = 0;
        $query->limit = 25;

        $results = $this->urlService->findUrls($query);

        foreach ($results->items as $result) {
            $output->writeln($result->url);
        }

        return self::SUCCESS;
    }
}
