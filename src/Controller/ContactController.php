<?php
/**
 * Controller\ContactController
 *
 * POST /api/contact → send()
 */
declare(strict_types=1);

namespace Controller;

use Service\ContactService;

class ContactController extends BaseController
{
    public function __construct(private readonly ContactService $service) {}

    public function send(): void
    {
        $body = $this->getJsonBody();

        if (!$body) {
            $this->error('Request body is required.');
        }

        try {
            $result = $this->service->send(
                name:    $this->str($body, 'name'),
                email:   $this->str($body, 'email'),
                reason:  $this->str($body, 'reason'),
                message: $this->str($body, 'message'),
            );
            $this->success($result, $result['message'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
        }
    }
}
