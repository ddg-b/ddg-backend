<?php

namespace App\Command;

use App\Service\GifToDatabase;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:gifs:add-to-db',
    description: 'Step 2 - Add new crawled gifs to the database',
)]
class AddNewGifsToDatabaseCommand extends Command
{
    public function __construct(
        private readonly GifToDatabase $gifToDatabase,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->gifToDatabase->addToDatabase();

        return Command::SUCCESS;
    }
}
