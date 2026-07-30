<?php
declare(strict_types=1);

namespace Model;

class Announcement
{
    public function __construct(
        public readonly int    $id,
        public readonly string $title,
        public readonly string $body,
        public readonly string $category,
        public readonly bool   $is_pinned,
        public readonly string $published_at,
        public readonly string $created_at,
        public readonly string $posted_by = 'Agape House',
        public readonly int    $commentCount = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'body'          => $this->body,
            'category'      => $this->category,
            'is_pinned'     => $this->is_pinned,
            'published_at'  => $this->published_at,
            'created_at'    => $this->created_at,
            'posted_by'     => $this->posted_by,
            'comment_count' => $this->commentCount,
        ];
    }

    public static function fromRow(array $row): self
    {
        return new self(
            id:           (int)  $row['id'],
            title:               $row['title'],
            body:                $row['body'],
            category:            $row['category'],
            is_pinned:    (bool) $row['is_pinned'],
            published_at:        $row['published_at'],
            created_at:          $row['created_at'],
            posted_by:           $row['posted_by']      ?? 'Agape House',
            commentCount:  (int) ($row['comment_count'] ?? 0),
        );
    }
}
