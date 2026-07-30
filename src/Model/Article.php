<?php
/**
 * Model\Article
 *
 * Represents a devotional or reflection article.
 */
declare(strict_types=1);

namespace Model;

class Article
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $title,
        public readonly string  $excerpt,
        public readonly string  $body,
        public readonly int     $readMinutes,
        public readonly string  $publishedAt,
        public readonly string  $postedBy = 'Agape House',
        public readonly ?int    $memberId = null,
        public readonly ?string $posterPicture = null,
        public readonly ?string $posterUsername = null,
        public readonly int     $commentCount = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'excerpt'        => $this->excerpt,
            'body'           => $this->body,
            'read_minutes'   => $this->readMinutes,
            'published_at'   => $this->publishedAt,
            'posted_by'      => $this->postedBy,
            'member_id'      => $this->memberId,
            'poster_picture' => $this->posterPicture,
            'poster_username'=> $this->posterUsername,
            'comment_count'  => $this->commentCount,
        ];
    }
}
