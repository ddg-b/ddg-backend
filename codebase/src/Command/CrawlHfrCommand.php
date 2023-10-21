<?php

namespace App\Command;

use App\Service\HfrCrawler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:crawl:hfr',
    description: 'Step 1 - Crawler HFR',
)]
class CrawlHfrCommand extends Command
{
    public function __construct(
        private readonly HfrCrawler $hfrCrawler,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->hfrCrawler->crawl()->addCrawledPosts();

        return Command::SUCCESS;
    }
}
