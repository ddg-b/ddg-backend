<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use App\Repository\StatRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StatRepository::class)]
#[ORM\Table(name: 'stats')]
class Stat
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'stats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gif $gif = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['gif:read'])]
    private ?\DateTimeImmutable $first_use = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'originalGifs')]
    #[Groups(['gif:read'])]
    private ?User $first_use_user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['gif:read'])]
    private ?\DateTimeInterface $last_use = null;

    #[ORM\ManyToOne(fetch: 'EAGER')]
    #[Groups(['gif:read'])]
    private ?User $last_use_user = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['gif:read'])]
    private ?int $count_used = null;

    public function getGif(): ?Gif
    {
        return $this->gif;
    }

    public function setGif(?Gif $gif): static
    {
        $this->gif = $gif;

        return $this;
    }

    public function getFirstUse(): ?\DateTimeImmutable
    {
        return $this->first_use;
    }

    public function setFirstUse(?\DateTimeImmutable $first_use): static
    {
        $this->first_use = $first_use;

        return $this;
    }

    public function getLastUse(): ?\DateTimeInterface
    {
        return $this->last_use;
    }

    public function setLastUse(?\DateTimeInterface $last_use): static
    {
        $this->last_use = $last_use;

        return $this;
    }

    public function getCountUsed(): ?int
    {
        return $this->count_used;
    }

    public function setCountUsed(?int $count_used): static
    {
        $this->count_used = $count_used;

        return $this;
    }

    public function getFirstUseUser(): ?User
    {
        return $this->first_use_user;
    }

    public function setFirstUseUser(?User $first_use_user): static
    {
        $this->first_use_user = $first_use_user;

        return $this;
    }

    public function getLastUseUser(): ?User
    {
        return $this->last_use_user;
    }

    public function setLastUseUser(?User $last_use_user): static
    {
        $this->last_use_user = $last_use_user;

        return $this;
    }
}
