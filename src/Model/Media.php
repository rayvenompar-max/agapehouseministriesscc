<?php
/**
 * Model\Media
 *
 * Represents a sermon, devotional, testimony, or worship video.
 */
declare(strict_types=1);

namespace Model;

class Media
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $title,
        public readonly string  $description,
        public readonly string  $type,
        public readonly string  $series,
        public readonly string  $postedBy,
        public readonly ?int    $memberId,
        public readonly ?string $posterPicture,
        public readonly ?string $posterUsername,
        public readonly int     $duration,
        public readonly string  $thumbnail,
        public readonly string  $videoUrl,
        public readonly bool    $featured,
        public readonly string  $publishedAt,
        public readonly int     $commentCount = 0,
    ) {}

    /** Human-readable duration: 38 min */
    public function formattedDuration(): string
    {
        $mins = (int) round($this->duration / 60);
        return $mins . ' min';
    }

    /**
     * Extract the YouTube video ID from a watch or short URL.
     * Returns null if the URL is not a YouTube URL.
     */
    public function youtubeId(): ?string
    {
        if (!$this->videoUrl) return null;
        if (preg_match(
            '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([a-zA-Z0-9_-]{11})/',
            $this->videoUrl,
            $m
        )) {
            return $m[1];
        }
        return null;
    }

    /**
     * Return the best available thumbnail URL.
     * Falls back to deriving it from the YouTube video ID so it always stays
     * in sync with the video URL without needing a separate DB update.
     */
    public function resolvedThumbnail(): string
    {
        if ($this->thumbnail !== '') {
            return $this->thumbnail;
        }
        $ytId = $this->youtubeId();
        return $ytId ? "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg" : '';
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'type'              => $this->type,
            'series'            => $this->series,
            'posted_by'         => $this->postedBy,
            'member_id'         => $this->memberId,
            'poster_picture'    => $this->posterPicture,
            'poster_username'   => $this->posterUsername,
            'duration'          => $this->duration,
            'duration_label'    => $this->formattedDuration(),
            'thumbnail'         => $this->resolvedThumbnail(),
            'video_url'         => $this->videoUrl,
            'featured'          => $this->featured,
            'published_at'      => $this->publishedAt,
            'comment_count'     => $this->commentCount,
        ];
    }
}
