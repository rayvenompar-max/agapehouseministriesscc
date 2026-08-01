<?php
/**
 * Repository\EventRepository
 */
declare(strict_types=1);

namespace Repository;

use Model\Event;
use PDO;

class EventRepository
{
    public function __construct(private readonly PDO $db) {}

    /** Return recurring (weekly) services — deduplicated by title + start_time. */
    public function findRecurring(): array
    {
        $stmt = $this->db->query(
            'SELECT * FROM events WHERE is_recurring = 1
             GROUP BY title, start_time
             ORDER BY FIELD(recur_day,"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"),
                      start_time ASC'
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /** Return upcoming one-off events from today forward — deduplicated by title + event_date. */
    public function findUpcoming(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM events WHERE is_recurring = 0 AND event_date >= CURDATE()
             GROUP BY title, event_date
             ORDER BY event_date ASC, start_time ASC"
        );
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findById(int $id): ?Event
    {
        $stmt = $this->db->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function insert(
        string $title,
        string $description,
        string $eventDate,
        string $startTime,
        string $location,
        bool   $hasLivestream,
        bool   $isRecurring,
        string $recurDay
    ): ?Event {
        $stmt = $this->db->prepare(
            'INSERT INTO events
                (title, description, event_date, start_time, location, has_livestream, is_recurring, recur_day)
             VALUES
                (:title, :description, :event_date, :start_time, :location, :has_livestream, :is_recurring, :recur_day)'
        );
        $stmt->execute([
            'title'          => $title,
            'description'    => $description,
            'event_date'     => $eventDate,
            'start_time'     => $startTime,
            'location'       => $location,
            'has_livestream' => (int) $hasLivestream,
            'is_recurring'   => (int) $isRecurring,
            'recur_day'      => $recurDay,
        ]);
        return $this->findById((int) $this->db->lastInsertId());
    }

    /** Delete an event and its registrations. */
    public function delete(int $id): bool
    {
        // Registrations are cascade-deleted if the FK is set; otherwise delete manually.
        $this->db->prepare('DELETE FROM event_registrations WHERE event_id = :id')
                 ->execute(['id' => $id]);
        $stmt = $this->db->prepare('DELETE FROM events WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // ── Registrations ──────────────────────────────────────────────────────────

    /**
     * Register a member for an event with a join type.
     * Uses INSERT … ON DUPLICATE KEY UPDATE to allow changing join type.
     * Returns 'created' | 'updated' | false on failure.
     */
    public function registerMember(int $eventId, int $memberId, string $joinType): string|false
    {
        $stmt = $this->db->prepare(
            'INSERT INTO event_registrations (event_id, member_id, join_type)
             VALUES (:event_id, :member_id, :join_type)
             ON DUPLICATE KEY UPDATE join_type = VALUES(join_type)'
        );
        $result = $stmt->execute([
            'event_id'  => $eventId,
            'member_id' => $memberId,
            'join_type' => $joinType,
        ]);
        if (!$result) return false;
        // rowCount() == 1 = new insert, 2 = updated existing
        return $stmt->rowCount() === 1 ? 'created' : 'updated';
    }

    /** Remove a registration. */
    public function cancelRegistration(int $eventId, int $memberId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM event_registrations WHERE event_id = :event_id AND member_id = :member_id'
        );
        return $stmt->execute(['event_id' => $eventId, 'member_id' => $memberId]);
    }

    /** Check if a member is already registered. Returns the row or null. */
    public function findRegistration(int $eventId, int $memberId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM event_registrations WHERE event_id = :event_id AND member_id = :member_id LIMIT 1'
        );
        $stmt->execute(['event_id' => $eventId, 'member_id' => $memberId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Get all registrations for an event, joined with member info.
     */
    public function findRegistrationsByEvent(int $eventId): array
    {
        $stmt = $this->db->prepare(
            'SELECT er.id, er.event_id, er.member_id, er.join_type, er.registered_at,
                    m.display_name, m.username, m.email
             FROM event_registrations er
             JOIN members m ON m.id = er.member_id
             WHERE er.event_id = :event_id
             ORDER BY er.registered_at ASC'
        );
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    /**
     * Get a summary of all events with their registration counts (for admin).
     */
    public function findAllWithRegistrationCounts(): array
    {
        $stmt = $this->db->query(
            'SELECT e.*,
                    COUNT(er.id) AS registrations_count
             FROM events e
             LEFT JOIN event_registrations er ON er.event_id = e.id
             GROUP BY e.id
             ORDER BY e.is_recurring DESC, e.event_date ASC, e.start_time ASC'
        );
        return $stmt->fetchAll();
    }

    private function hydrate(array $row): Event
    {
        return new Event(
            id:            (int)  $row['id'],
            title:                $row['title'],
            description:          $row['description'],
            eventDate:            $row['event_date'],
            startTime:            $row['start_time'],
            location:             $row['location']     ?? '',
            hasLivestream: (bool) $row['has_livestream'],
            isRecurring:   (bool) $row['is_recurring'],
            recurDay:             $row['recur_day']    ?? '',
        );
    }
}
