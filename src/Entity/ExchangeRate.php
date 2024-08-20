<?php
// Defines the ExchangeRate entity for ORM mapping with the database
namespace App\Entity;

use App\Dto\ExchangeRateDto;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\Repository\ExchangeRateRepository")]
#[ORM\Table(name: "exchange_rate", uniqueConstraints: [new ORM\UniqueConstraint(columns: ["currency", "date"])])]
class ExchangeRate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: "float")]
    private ?float $rate = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $date = null;

    // Populate the entity properties from a Dto
    public function fromDto(ExchangeRateDto $dto): void
    {
        $this->currency = $dto->currency;
        $this->rate = $dto->rate;
        $this->date = $dto->date;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): void
    {
        $this->rate = $rate;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void
    {
        $this->date = $date;
    }
}