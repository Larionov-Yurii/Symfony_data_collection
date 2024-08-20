<?php
// Defines a route for converting currencies
namespace App\Controller;

use App\Service\ExchangeRateService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CurrencyController extends AbstractController
{
    private $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    #[Route('/api/convert', methods: ['GET'])]
    public function convert(Request $request): JsonResponse
    {
        $amount = $request->query->get('amount');
        $fromCurrency = $request->query->get('from');
        $toCurrency = $request->query->get('to');

        // Validate input parameters
        if (empty($amount) || !is_numeric($amount) || $amount <= 0) {
            return new JsonResponse(['error' => 'Invalid amount provided.'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($fromCurrency) || empty($toCurrency)) {
            return new JsonResponse(['error' => 'Both fromCurrency and toCurrency must be provided.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $convertedAmount = $this->exchangeRateService->convertCurrency((float) $amount, $fromCurrency, $toCurrency);
            return new JsonResponse(['amount' => $convertedAmount], Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while processing your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}