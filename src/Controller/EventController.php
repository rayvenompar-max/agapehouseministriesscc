<?php
/**
 * Controller\EventController
 *
 * GET /api/events/weekly    → getWeekly()
 * GET /api/events/upcoming  → getUpcoming()
 */
declare(strict_types=1);

namespace Controller;

use Service\EventService;

class EventController extends BaseController
{
    public function __construct(private readonly EventService $service) {}

    public function getWeekly(): void
    {
        $this->success($this->service->getWeeklySchedule());
    }

    public function getUpcoming(): void
    {
        $this->success($this->service->getUpcomingEvents());
    }

    /** POST /api/events/{id}/register — member registers for an event. */
    public function register(int $eventId): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('You must be logged in to register for an event.', 401);
            return;
        }

        $memberId = (int) $_SESSION['member']['id'];
        $data     = $this->getJsonBody() ?? [];
        $joinType = trim((string) ($data['join_type'] ?? 'in_person'));

        try {
            $result = $this->service->registerMember($eventId, $memberId, $joinType);
            $this->success($result, 'Registration saved.');
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    /** DELETE /api/events/{id}/register — member cancels their registration. */
    public function cancelRegistration(int $eventId): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->error('Not logged in.', 401);
            return;
        }
        $memberId = (int) $_SESSION['member']['id'];
        $this->service->cancelRegistration($eventId, $memberId);
        $this->success([], 'Registration cancelled.');
    }

    /** GET /api/events/{id}/register — get current member's registration status. */
    public function getRegistrationStatus(int $eventId): void
    {
        if (empty($_SESSION['member']['id'])) {
            $this->success(['registered' => false]);
            return;
        }
        $memberId = (int) $_SESSION['member']['id'];
        $reg = $this->service->getRegistrationStatus($eventId, $memberId);
        $this->success([
            'registered' => $reg !== null,
            'join_type'  => $reg['join_type'] ?? null,
        ]);
    }

    /** GET /api/events/{id}/registrations — admin: list all registrants for an event. */
    public function getRegistrations(int $eventId): void
    {
        // Admin-only: check admin session
        if (empty($_SESSION['admin'])) {
            $this->error('Admin access required.', 403);
            return;
        }
        try {
            $data = $this->service->getRegistrations($eventId);
            $this->success($data);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        }
    }

    /** GET /api/events/all — admin: all events with registration counts. */
    public function getAllWithCounts(): void
    {
        if (empty($_SESSION['admin'])) {
            $this->error('Admin access required.', 403);
            return;
        }
        $this->success($this->service->getAllEventsWithCounts());
    }

    /** DELETE /api/events/{id} — admin: delete an event and its registrations. */
    public function deleteEvent(int $eventId): void
    {
        if (empty($_SESSION['admin'])) {
            $this->error('Admin access required.', 403);
            return;
        }
        $deleted = $this->service->deleteEvent($eventId);
        if ($deleted) {
            $this->success([], 'Event deleted.');
        } else {
            $this->error('Event not found.', 404);
        }
    }

    public function create(): void
    {
        $data = $this->getJsonBody();
        if (!$data) { $this->error('Request body is required.'); return; }

        try {
            $event = $this->service->create(
                title:        $this->str($data, 'title'),
                description:  $this->str($data, 'description'),
                eventDate:    $this->str($data, 'event_date'),
                startTime:    $this->str($data, 'start_time'),
                location:     $this->str($data, 'location'),
                hasLivestream:(bool) ($data['has_livestream'] ?? false),
                isRecurring:  (bool) ($data['is_recurring']  ?? false),
                recurDay:     $this->str($data, 'recur_day'),
            );
            $this->success($event, 'Event created.', 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        }
    }
}
