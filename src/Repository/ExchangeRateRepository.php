<?php
// Defines the repository for managing ExchangeRate entities
namespace App\Repository;

use App\Entity\ExchangeRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExchangeRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExchangeRate::class);
    }

    // Saves an exchange rate entity to the database
    public function save(ExchangeRate $rate): void
    {
        $this->_em->persist($rate);
        $this->_em->flush();
    }

    // Finds an exchange rate by currency and date
    public function findRateByCurrency(string $currency, \DateTimeInterface $date): ?float
    {
    $qb = $this->createQueryBuilder('er')
        ->where('er.currency = :currency')
        ->andWhere('er.date = :date')
        ->setParameter('currency', $currency)
        ->setParameter('date', $date->format('Y-m-d'));

    $rate = $qb->getQuery()->getOneOrNullResult();

    return $rate ? $rate->getRate() : null;
    }

    // Finds all exchange rates for a specific date
    public function findRatesByDate(\DateTimeInterface $date): array
    {
        return $this->findBy(['date' => $date]);
    }

    // Finds all unique currencies stored in the database
    public function findAllCurrencies(): array
    {
        $qb = $this->createQueryBuilder('er')
            ->select('DISTINCT er.currency');

        return array_map('current', $qb->getQuery()->getResult());
    }
}
