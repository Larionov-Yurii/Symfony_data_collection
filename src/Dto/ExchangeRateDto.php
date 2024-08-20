<?php
// Defines a Data Transfer Object for exchange rate data
namespace App\Dto;

class ExchangeRateDto
{
    public $currency;
    public $rate;
    public $date;

    // Constructor initializes the Dto with currency, rate, and date
    public function __construct(string $currency, float $rate, \DateTimeInterface $date)
    {
        $this->currency = $currency;
        $this->rate = $rate;
        $this->date = $date;
    }
}