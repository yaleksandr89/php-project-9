<?php

declare(strict_types=1);

namespace App\Entity;

readonly class UrlCheck
{
    public function __construct(
        private ?int $id,
        private int $urlId,
        private ?int $statusCode,
        private ?string $h1,
        private ?string $title,
        private ?string $description,
        private string $createdAt
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlId(): int
    {
        return $this->urlId;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
}
