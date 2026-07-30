<?php
/**
 * Service\DonationService
 *
 * Business logic for the Give section.
 * (Payment gateway integration would hook in here.)
 */
declare(strict_types=1);

namespace Service;

use Repository\DonationRepository;

class DonationService
{
    private const TIERS = [
        'seed'    => 10.00,
        'grow'    => 35.00,
        'harvest' => 100.00,
    ];

    public function __construct(private readonly DonationRepository $repo) {}

    /**
     * Initiate a donation.
     *
     * @throws \InvalidArgumentException on bad input
     */
    public function initiate(
        string $donorName,
        string $donorEmail,
        float  $amount,
        string $frequency,
        string $tier = 'custom'
    ): array {
        // Validate amount
        if ($amount < 1.00) {
            throw new \InvalidArgumentException('Minimum donation is $1.00.');
        }
        if ($amount > 100000.00) {
            throw new \InvalidArgumentException('Please contact us for large gifts.');
        }

        // Validate frequency
        if (!in_array($frequency, ['one_time', 'monthly'], true)) {
            throw new \InvalidArgumentException('Invalid frequency.');
        }

        // Validate email
        if (!filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address.');
        }

        // Resolve tier label from amount if not supplied
        if ($tier === 'custom') {
            foreach (self::TIERS as $label => $preset) {
                if (abs($amount - $preset) < 0.01) {
                    $tier = $label;
                    break;
                }
            }
        }

        $donation = $this->repo->create(
            donorName:  trim($donorName) ?: 'Anonymous',
            donorEmail: $donorEmail,
            amount:     $amount,
            currency:   'USD',
            frequency:  $frequency,
            tier:       $tier,
        );

        // TODO: pass $donation->id to a real payment gateway (Stripe, PayMongo, etc.)

        return [
            'success'   => true,
            'message'   => 'Thank you! Your gift is being processed.',
            'donation'  => $donation->toArray(),
        ];
    }

    public function getStats(): array
    {
        return [
            'total_given' => $this->repo->totalGiven(),
        ];
    }
}
