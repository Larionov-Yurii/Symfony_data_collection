<?php
/**
 * Define behavior checks for methods for updating exchange rates, 
 * converting currencies, and handling invalid input data.
 */
namespace App\Tests\Unit\Service;

use App\Service\ExchangeRateService;
use App\Repository\ExchangeRateRepository;
use App\Entity\ExchangeRate;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

class ExchangeRateServiceTest extends TestCase
{
    private $exchangeRateRepository;
    private $entityManager;
    private $logger;
    private $exchangeRateService;

    // Sets up the necessary mocks and the service under test
    protected function setUp(): void
    {
        $this->exchangeRateRepository = $this->createMock(ExchangeRateRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->exchangeRateService = new ExchangeRateService(
            $this->exchangeRateRepository,
            $this->entityManager,
            'ECB',
            $this->logger
        );
    }

    // Tests the behavior of the updateRates method when rates already exist for today
    public function testUpdateRatesWithExistingRates(): void
    {
        // Mock the repository to return a non-empty array indicating that rates for today already exist
        $this->exchangeRateRepository->method('findRatesByDate')
            ->willReturn([new \stdClass()]);

        // Expect that no new rates are persisted when rates for today already exist
        $this->entityManager->expects($this->never())->method('persist');

        $this->exchangeRateService->updateRates();
    }

    // Tests the conversion of currency from one type to another
    public function testConvertCurrency(): void
    {
        $today = new \DateTime('today');
        
        // Create mock ExchangeRate entities for USD and GBP
        $usdRateEntity = $this->createMock(ExchangeRate::class);
        $usdRateEntity->method('getRate')->willReturn(1.1084);
        $usdRateEntity->method('getCurrency')->willReturn('USD');
        $usdRateEntity->method('getDate')->willReturn($today);

        $gbpRateEntity = $this->createMock(ExchangeRate::class);
        $gbpRateEntity->method('getRate')->willReturn(0.85194);
        $gbpRateEntity->method('getCurrency')->willReturn('GBP');
        $gbpRateEntity->method('getDate')->willReturn($today);

        // Mock the repository to return the mock ExchangeRate entities for respective currencies
        $this->exchangeRateRepository->method('findRateByCurrency')
            ->willReturnMap([
                ['USD', $today, $usdRateEntity], 
                ['GBP', $today, $gbpRateEntity] 
            ]);

            // Mock the repository to return a list of supported currencies
        $this->exchangeRateRepository->method('findAllCurrencies')
            ->willReturn(['USD', 'GBP']);

        $convertedAmount = $this->exchangeRateService->convertCurrency(100, 'USD', 'GBP');

        $expectedAmount = 100 * (0.85194 / 1.1084);

        $this->assertEquals(round($expectedAmount, 2), round($convertedAmount, 2));
    }

    // Tests the conversion of currency when an invalid currency code is provided
    public function testConvertCurrencyWithInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $today = new \DateTime('today');

        // Mock the repository to return valid rate for USD but null for an invalid currency 'XYZ'
        $this->exchangeRateRepository->method('findRateByCurrency')
            ->willReturnMap([
                ['USD', $today, $this->createMock(ExchangeRate::class)], 
                ['XYZ', $today, null]
            ]);

        $this->exchangeRateRepository->method('findAllCurrencies')
            ->willReturn(['USD']);

         // Invoke the convertCurrency method with an invalid currency code
        $this->exchangeRateService->convertCurrency(100, 'USD', 'XYZ');
    }
}
