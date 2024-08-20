<?php
// Defines a Symfony console command to update exchange rates
namespace App\Command;

use App\Service\ExchangeRateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateExchangeRatesCommand extends Command
{
    // The name of the command to be used in the console
    protected static $defaultName = 'app:update-exchange-rates';

    // Service dependency to interact with exchange rates
    private $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:update-exchange-rates')
            ->setDescription('Fetches the latest exchange rates and updates the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->exchangeRateService->updateRates();
        $output->writeln('Exchange rates updated successfully.');
        return Command::SUCCESS;
    }
}