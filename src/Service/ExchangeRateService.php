<?php
// Defines the service for fetching, updating, and converting exchange rates
namespace App\Service;

use App\Repository\ExchangeRateRepository;
use App\Entity\ExchangeRate;
use App\Dto\ExchangeRateDto;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ExchangeRateService
{
    private $exchangeRateRepository;
    private $entityManager;
    private $dataSource;
    private $logger;

    public function __construct(
        ExchangeRateRepository $exchangeRateRepository,
        EntityManagerInterface $entityManager,
        string $dataSource,
        LoggerInterface $logger
    ) {
        $this->exchangeRateRepository = $exchangeRateRepository;
        $this->entityManager = $entityManager;
        $this->dataSource = $dataSource;
        $this->logger = $logger;
    }

    // Updates exchange rates from the specified data source
    public function updateRates(): void
    {
        try {
            $ratesData = $this->fetchRatesFromSource();
            $today = new \DateTime('today');

            // Check if rates for today already exist
            $existingRates = $this->exchangeRateRepository->findRatesByDate($today);
            if (!empty($existingRates)) {
                $this->logger->info('Exchange rates for today already exist. Skipping update.');
                return;
            }

            // Persist new rates if they do not exist
            foreach ($ratesData as $rateDto) {
                $rate = new ExchangeRate();
                $rate->fromDto($rateDto);
                $this->entityManager->persist($rate);
            }
            $this->entityManager->flush();

            $this->logger->info('Exchange rates updated successfully.');
        } catch (\Exception $e) {
            $this->logger->error('Failed to update exchange rates: ' . $e->getMessage());
            throw $e;
        }
    }

    // Fetches exchange rates data from the configured data source
    private function fetchRatesFromSource(): array
    {
        $ratesDto = [];

        try {
            if ($this->dataSource === 'ECB') {
                $ratesDto = $this->fetchRatesFromECB();
            } elseif ($this->dataSource === 'CBR') {
                $ratesDto = $this->fetchRatesFromCBR();
            } else {
                throw new \InvalidArgumentException('Invalid data source specified.');
            }
        } catch (\Exception $e) {
            $this->logger->error('Error fetching exchange rates: ' . $e->getMessage());
            throw $e;
        }

        return $ratesDto;
    }

    private function fetchRatesFromECB(): array
    {
        $ratesDto = [];

        $xml = @simplexml_load_file('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        if ($xml === false) {
            throw new \RuntimeException('Failed to load data from ECB.');
        }

        // Extract date and rates from ECB XML
        $date = new \DateTime((string)$xml->Cube->Cube['time']);
        foreach ($xml->Cube->Cube->Cube as $rate) {
            $currency = (string)$rate['currency'];
            $rateValue = (float)$rate['rate'];
            $ratesDto[] = new ExchangeRateDto($currency, $rateValue, $date);
        }

        return $ratesDto;
    }

    private function fetchRatesFromCBR(): array
    {
        $ratesDto = [];

        $xml = @simplexml_load_file('https://www.cbr.ru/scripts/XML_daily.asp');
        if ($xml === false) {
            throw new \RuntimeException('Failed to load data from CBR.');
        }

        // Extract date and rates from CBR XML
        $date = \DateTime::createFromFormat('d.m.Y', (string)$xml['Date']);
        if ($date === false) {
            throw new \RuntimeException('Failed to parse date from CBR.');
        }

        foreach ($xml->Valute as $rate) {
            $currency = (string)$rate->CharCode;
            $value = (float)str_replace(',', '.', (string)$rate->Value);
            $nominal = (int)$rate->Nominal;
            $normalizedRate = $value / $nominal;
            $ratesDto[] = new ExchangeRateDto($currency, $normalizedRate, $date);
        }

        return $ratesDto;
    }

    // Converts an amount from one currency to another using the stored exchange rates
    public function convertCurrency(float $amount, string $fromCurrency, string $toCurrency): float
    {
        $this->validateCurrency($fromCurrency);
        $this->validateCurrency($toCurrency);

        $today = new \DateTime('today');

        $fromRate = $this->exchangeRateRepository->findRateByCurrency($fromCurrency, $today);
        $toRate = $this->exchangeRateRepository->findRateByCurrency($toCurrency, $today);

        if ($fromRate === null || $toRate === null) {
            $this->logger->warning("Currency conversion failed due to missing rate: {$fromCurrency} to {$toCurrency}.");
            throw new \InvalidArgumentException('Invalid currency code or missing exchange rate.');
        }

        return ($amount / $fromRate) * $toRate;
    }

    // Validates if a currency code is supported
    private function validateCurrency(string $currency): void
    {
        $supportedCurrencies = $this->exchangeRateRepository->findAllCurrencies();

        if (!in_array($currency, $supportedCurrencies)) {
            $this->logger->warning('Unsupported currency code: ' . $currency);
            throw new \InvalidArgumentException(sprintf('Unsupported currency code: %s', $currency));
        }
    }
}
