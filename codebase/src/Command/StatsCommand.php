<?php

namespace App\Command;

use App\Service\GifToDatabase;
use App\Service\Stats;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:stats',
    description: 'Step 3 - Run the statistics',
)]
class StatsCommand extends Command
{
    public function __construct(
        private readonly Stats $stats,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->stats->run();

        return Command::SUCCESS;
    }
}
