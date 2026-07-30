<?php
/**
 * Service\EventService
 *
 * Business logic for the Events section.
 */
declare(strict_types=1);

namespace Service;

use Repository\EventRepository;
use Repository\NotificationRepository;
use Repository\MemberRepository;

class EventService
{
    public function __construct(
        private readonly EventRepository $repo,
        private readonly NotificationRepository $notificationRepo,
        private readonly MemberRepository $memberRepo
    ) {}

    private const RECUR_DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    public function getWeeklySchedule(): array
    {
        return array_map(fn($e) => $e->toArray(), $this->repo->findRecurring());
    }

    public function getUpcomingEvents(): array
    {
        return array_map(fn($e) => $e->toArray(), $this->repo->findUpcoming());
    }

    /**
     * Register a member for an event.
     * @throws \InvalidArgumentException on bad input
     * @throws \RuntimeException if event not found
     */
    public function registerMember(int $eventId, int $memberId, string $joinType): array
    {
        if (!in_array($joinType, ['online', 'in_person'], true)) {
            throw new \InvalidArgumentException('join_type must be "online" or "in_person".');
        }

        $event = $this->repo->findById($eventId);
        if (!$event) {
            throw new \RuntimeException('Event not found.');
        }

        $result = $this->repo->registerMember($eventId, $memberId, $joinType);
        if ($result === false) {
            throw new \RuntimeException('Could not save registration.');
        }

        // Return current registration status
        $reg = $this->repo->findRegistration($eventId, $memberId);
        return [
            'event_id'      => $eventId,
            'member_id'     => $memberId,
            'join_type'     => $joinType,
            'registered_at' => $reg['registered_at'] ?? null,
            'action'        => $result, // 'created' or 'updated'
        ];
    }

    /** Cancel a member's registration. */
    public function cancelRegistration(int $eventId, int $memberId): void
    {
        $this->repo->cancelRegistration($eventId, $memberId);
    }

    /** Get registration status for a specific member + event. */
    public function getRegistrationStatus(int $eventId, int $memberId): ?array
    {
        return $this->repo->findRegistration($eventId, $memberId);
    }

    /** Get all registrants for an event (admin use). */
    public function getRegistrations(int $eventId): array
    {
        $event = $this->repo->findById($eventId);
        if (!$event) {
            throw new \RuntimeException('Event not found.');
        }
        return [
            'event'         => $event->toArray(),
            'registrations' => $this->repo->findRegistrationsByEvent($eventId),
        ];
    }

    /** Get all events with registration counts (admin use). */
    public function getAllEventsWithCounts(): array
    {
        return $this->repo->findAllWithRegistrationCounts();
    }

    public function create(
        string $title,
        string $description,
        string $eventDate,
        string $startTime,
        string $location,
        bool   $hasLivestream,
        bool   $isRecurring,
        string $recurDay
    ): array {
        if (trim($title) === '') {
            throw new \InvalidArgumentException('Title is required.');
        }
        if (!$isRecurring && !$eventDate) {
            throw new \InvalidArgumentException('Event date is required for non-recurring events.');
        }
        if (!$startTime) {
            throw new \InvalidArgumentException('Start time is required.');
        }
        if ($isRecurring && !in_array($recurDay, self::RECUR_DAYS, true)) {
            throw new \InvalidArgumentException('A valid day of week is required for recurring events.');
        }

        // For recurring events, store a near-future placeholder date matching the recur_day
        if ($isRecurring) {
            $eventDate = $this->nextOccurrence($recurDay);
        }

        $event = $this->repo->insert(
            trim($title),
            trim($description),
            $eventDate,
            $startTime,
            trim($location),
            $hasLivestream,
            $isRecurring,
            $isRecurring ? $recurDay : ''
        );

        // Notify all active members about the new event
        if ($event) {
            $this->notifyAllMembers($event->id, $title);
        }

        return $event?->toArray() ?? [];
    }

    /**
     * Notify all active members about a new event.
     * Uses actor_id = 0 (system/broadcast) so the notification is never skipped
     * and does not join to a member row for display.
     */
    private function notifyAllMembers(int $eventId, string $eventTitle): void
    {
        $memberIds = $this->memberRepo->getAllActiveMemberIds();

        foreach ($memberIds as $memberId) {
            $this->notificationRepo->createBroadcast(
                recipientId:  $memberId,
                type:         'new_event',
                targetType:   'event',
                targetId:     $eventId,
                targetTitle:  $eventTitle
            );
        }
    }

    /** Return the date string (Y-m-d) of the next occurrence of a given weekday. */
    private function nextOccurrence(string $day): string
    {
        $ts = strtotime("next {$day}");
        return date('Y-m-d', $ts ?: time());
    }
}
