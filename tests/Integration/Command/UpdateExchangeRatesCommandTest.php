<?php
// Defining how the command (app:update-exchange-rates) updates exchange rates in the database
namespace App\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use App\Repository\ExchangeRateRepository;

class UpdateExchangeRatesCommandTest extends KernelTestCase
{
    public function testExecute(): void
    {
        self::bootKernel();

        $application = new Application(self::$kernel);

        $command = $application->find('app:update-exchange-rates');

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Exchange rates updated successfully.', $output);

        // Assert database contains new exchange rates
        $repository = self::getContainer()->get(ExchangeRateRepository::class);
        $rate = $repository->findRateByCurrency('USD', new \DateTime('today'));
        $this->assertNotNull($rate);
    }
}
