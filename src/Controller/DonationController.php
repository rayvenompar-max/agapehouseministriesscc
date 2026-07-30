<?php
/**
 * Controller\DonationController
 *
 * POST /api/donations       → initiate()
 * GET  /api/donations/stats → stats()
 */
declare(strict_types=1);

namespace Controller;

use Service\DonationService;

class DonationController extends BaseController
{
    public function __construct(private readonly DonationService $service) {}

    public function initiate(): void
    {
        $body = $this->getJsonBody();

        if (!$body) {
            $this->error('Request body is required.');
        }

        try {
            $result = $this->service->initiate(
                donorName:  $this->str($body, 'donor_name'),
                donorEmail: $this->str($body, 'donor_email'),
                amount:     $this->float($body, 'amount'),
                frequency:  $this->str($body, 'frequency', 'one_time'),
                tier:       $this->str($body, 'tier', 'custom'),
            );
            $this->success($result, $result['message'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }

    public function stats(): void
    {
        $this->success($this->service->getStats());
    }
}
