<?php
/**
 * Service\ContactService
 *
 * Business logic for the Connect / contact form.
 */
declare(strict_types=1);

namespace Service;

use Repository\ContactRepository;

class ContactService
{
    private const ALLOWED_REASONS = [
        'Just saying hi',
        'Prayer request',
        'Questions about faith',
        'Volunteering',
        'Technical issue',
    ];

    public function __construct(private readonly ContactRepository $repo) {}

    /**
     * @throws \InvalidArgumentException on validation failure
     */
    public function send(string $name, string $email, string $reason, string $message): array
    {
        // Validate
        $name    = trim($name);
        $email   = trim($email);
        $message = trim($message);

        if ($name === '') {
            throw new \InvalidArgumentException('Name is required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('A valid email address is required.');
        }
        if (!in_array($reason, self::ALLOWED_REASONS, true)) {
            throw new \InvalidArgumentException('Invalid reason selected.');
        }
        if (strlen($message) < 5) {
            throw new \InvalidArgumentException('Message is too short.');
        }
        if (strlen($message) > 3000) {
            throw new \InvalidArgumentException('Message must be 3000 characters or fewer.');
        }

        $contact = $this->repo->create($name, $email, $reason, $message);

        return [
            'success' => true,
            'message' => "Thanks, {$name}! We'll be in touch.",
            'id'      => $contact->id,
        ];
    }
}
