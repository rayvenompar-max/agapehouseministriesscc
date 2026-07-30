<?php
/**
 * Model\Event
 *
 * Represents a scheduled church gathering or livestream.
 */
declare(strict_types=1);

namespace Model;

class Event
{
    public function __construct(
        public readonly int    $id,
        public readonly string $title,
        public readonly string $description,
        public readonly string $eventDate,      // YYYY-MM-DD
        public readonly string $startTime,      // HH:MM
        public readonly string $location,
        public readonly bool   $hasLivestream,
        public readonly bool   $isRecurring,
        public readonly string $recurDay,       // 'Sunday', 'Wednesday', etc. (empty if not recurring)
    ) {}

    public function dayLabel(): string
    {
        // For recurring events, use the recur_day name + formatted time
        if ($this->isRecurring && $this->recurDay !== '') {
            return strtoupper(substr($this->recurDay, 0, 3))
                . ' · ' . date('g:iA', strtotime($this->startTime));
        }
        // For one-off events, use the actual event date
        return strtoupper(substr(date('D', strtotime($this->eventDate)), 0, 3))
            . ' · ' . date('g:iA', strtotime($this->startTime));
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'event_date'    => $this->eventDate,
            'start_time'    => $this->startTime,
            'day_label'     => $this->dayLabel(),
            'location'      => $this->location,
            'has_livestream'=> $this->hasLivestream,
            'is_recurring'  => $this->isRecurring,
            'recur_day'     => $this->recurDay,
        ];
    }
}
