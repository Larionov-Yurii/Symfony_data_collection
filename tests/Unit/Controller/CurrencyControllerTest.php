<?php
/**
 * Defines validation for processing currency conversion requests, 
 * including valid conversions, invalid inputs, and missing parameters
 */
namespace App\Tests\Unit\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Service\ExchangeRateService;
use PHPUnit\Framework\MockObject\MockObject;

class CurrencyControllerTest extends WebTestCase
{
    private MockObject $exchangeRateService;

    // Creating a mock of the ExchangeRateService
    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock ExchangeRateService
        $this->exchangeRateService = $this->createMock(ExchangeRateService::class);

        // Configure the mock to return 75.0 for any convertCurrency call
        $this->exchangeRateService->method('convertCurrency')
            ->willReturn(75.0);
    }

    public function testConvertCurrencySuccess(): void
    {
        $client = static::createClient();

        // Replace the real ExchangeRateService with the mock service in the container
        $client->getContainer()->set(ExchangeRateService::class, $this->exchangeRateService);

        // Perform a GET request to the /api/convert endpoint with valid parameters
        $client->request('GET', '/api/convert', [
            'amount' => 100,
            'from' => 'USD',
            'to' => 'GBP',
        ]);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        // Assert that the response contains the 'amount' key and the expected value
        $this->assertArrayHasKey('amount', $data);
        $this->assertEquals(75.0, $data['amount']);
    }

    public function testConvertCurrencyWithInvalidAmount(): void
    {
        $client = static::createClient();
        
        $client->getContainer()->set(ExchangeRateService::class, $this->exchangeRateService);

        // Perform a GET request to the /api/convert endpoint with an invalid amount
        $client->request('GET', '/api/convert', [
            'amount' => 'invalid',
            'from' => 'USD',
            'to' => 'GBP',
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testConvertCurrencyWithMissingParameters(): void
    {
        $client = static::createClient();
        
        $client->getContainer()->set(ExchangeRateService::class, $this->exchangeRateService);

        // Perform a GET request to the /api/convert endpoint with missing 'from' and 'to' parameters
        $client->request('GET', '/api/convert', [
            'amount' => 100,
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }
}
