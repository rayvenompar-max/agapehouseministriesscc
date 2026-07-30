<?php
/**
 * Repository\DonationRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\Donation;
use PDO;

class DonationRepository
{
    public function __construct(private readonly PDO $db) {}

    public function create(
        string $donorName,
        string $donorEmail,
        float  $amount,
        string $currency,
        string $frequency,
        string $tier
    ): Donation {
        $stmt = $this->db->prepare(
            'INSERT INTO donations (donor_name, donor_email, amount, currency, frequency, tier, status, created_at)
             VALUES (:donor_name, :donor_email, :amount, :currency, :frequency, :tier, :status, NOW())'
        );
        $stmt->execute([
            'donor_name'  => $donorName,
            'donor_email' => $donorEmail,
            'amount'      => $amount,
            'currency'    => $currency,
            'frequency'   => $frequency,
            'tier'        => $tier,
            'status'      => 'pending',
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function findById(int $id): ?Donation
    {
        $stmt = $this->db->prepare('SELECT * FROM donations WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function totalGiven(): float
    {
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM donations WHERE status = 'completed'"
        );
        return (float) $stmt->fetchColumn();
    }

    private function hydrate(array $row): Donation
    {
        return new Donation(
            id:          (int)   $row['id'],
            donorName:           $row['donor_name'],
            donorEmail:          $row['donor_email'],
            amount:      (float) $row['amount'],
            currency:            $row['currency'],
            frequency:           $row['frequency'],
            tier:                $row['tier'],
            status:              $row['status'],
            createdAt:           $row['created_at'],
        );
    }
}
