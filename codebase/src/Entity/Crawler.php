<?php

namespace App\Entity;

use App\Repository\CrawlerRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CrawlerRepository::class)]
class Crawler
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $page = null;

    #[ORM\Column]
    private ?DateTimeImmutable $date = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $src = null;

    #[ORM\Column]
    private bool $new = true;

    #[ORM\Column]
    private bool $stats = false;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $origin = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    #[ORM\ManyToOne]
    private ?Gif $gif = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getSrc(): ?string
    {
        return $this->src;
    }

    public function setSrc(string $src): static
    {
        $this->src = $src;

        return $this;
    }

    public function isNew(): ?bool
    {
        return $this->new;
    }

    public function setNew(bool $new): static
    {
        $this->new = $new;

        return $this;
    }

    public function isStats(): ?bool
    {
        return $this->stats;
    }

    public function setStats(bool $stats): static
    {
        $this->stats = $stats;

        return $this;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }

    public function setOrigin(?int $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGif(): ?Gif
    {
        return $this->gif;
    }

    public function setGif(?Gif $gif): static
    {
        $this->gif = $gif;

        return $this;
    }
}
