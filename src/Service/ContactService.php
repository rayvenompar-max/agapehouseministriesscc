<?php
/**
 * Service\ContactService
 *
 * Business logic for the Connect / contact form.
 */
declare(strict_types=1);

namespace Service;

use Repository\ContactRepository;
use Repository\NotificationRepository;

class ContactService
{
    private const ALLOWED_REASONS = [
        'Just saying hi',
        'Prayer request',
        'Questions about faith',
        'Volunteering',
        'Technical issue',
    ];

    public function __construct(
        private readonly ContactRepository      $repo,
        private readonly NotificationRepository $notifRepo,
    ) {}

    /**
     * @throws \InvalidArgumentException on validation failure
     */
    public function send(
        string $name,
        string $email,
        string $reason,
        string $message,
        ?int   $memberId = null
    ): array {
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

        $contact = $this->repo->create($name, $email, $reason, $message, $memberId);

        return [
            'success' => true,
            'message' => "Thanks, {$name}! We'll be in touch.",
            'id'      => $contact->id,
        ];
    }

    /** Return all contact messages as plain arrays (admin use). */
    public function getAll(): array
    {
        return array_map(
            fn($m) => $m->toArray(),
            $this->repo->findAll()
        );
    }

    /** Mark a single message as read. */
    public function markRead(int $id): void
    {
        $this->repo->markRead($id);
    }

    /**
     * Admin sends a reply in the chat thread.
     * Creates a chat message row, marks the contact as 'replied',
     * and fires a notification to the member (if the contact has a member_id).
     *
     * @throws \InvalidArgumentException
     */
    public function adminReply(int $contactMessageId, string $body): array
    {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('Reply cannot be empty.');
        }
        if (strlen($body) > 3000) {
            throw new \InvalidArgumentException('Reply must be 3000 characters or fewer.');
        }

        $contact = $this->repo->findById($contactMessageId);
        if (!$contact) {
            throw new \InvalidArgumentException('Message not found.');
        }

        // Save the admin chat message
        $chatMsg = $this->repo->addChatMessage($contactMessageId, 'admin', 0, $body);

        // Update contact status to 'replied'
        $this->repo->markReplied($contactMessageId);

        // Notify the member if they are linked
        if ($contact->memberId && $contact->memberId > 0) {
            $subject = 'Re: ' . mb_substr($contact->reason, 0, 60);
            $this->notifRepo->createBroadcast(
                $contact->memberId,
                'contact_reply',
                'contact_message',
                $contactMessageId,
                $subject
            );
        }

        return $chatMsg;
    }

    /**
     * A logged-in member sends a follow-up message in an existing thread.
     *
     * @throws \InvalidArgumentException
     */
    public function memberReply(int $contactMessageId, int $memberId, string $body): array
    {
        $body = trim($body);
        if ($body === '') {
            throw new \InvalidArgumentException('Message cannot be empty.');
        }
        if (strlen($body) > 3000) {
            throw new \InvalidArgumentException('Message must be 3000 characters or fewer.');
        }

        $contact = $this->repo->findById($contactMessageId);
        if (!$contact || $contact->memberId !== $memberId) {
            throw new \InvalidArgumentException('Conversation not found.');
        }

        $chatMsg = $this->repo->addChatMessage($contactMessageId, 'member', $memberId, $body);

        // Mark back to 'read' so admin knows there is a new member message
        $this->repo->markRead($contactMessageId);

        return $chatMsg;
    }

    /** Return full chat thread (original message + all replies). */
    public function getThread(int $contactMessageId): array
    {
        $contact = $this->repo->findById($contactMessageId);
        if (!$contact) {
            return [];
        }
        return [
            'contact'  => $contact->toArray(),
            'messages' => $this->repo->getChatMessages($contactMessageId),
        ];
    }

    /**
     * Return all contact message threads for a member.
     * Each thread includes unread admin reply count.
     */
    public function getMemberThreads(int $memberId): array
    {
        return $this->repo->findThreadsByMember($memberId);
    }
}
