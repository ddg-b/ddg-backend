<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\UserRepository;
use App\State\UserProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => 'user:read'],
        ),
        new GetCollection(
            uriTemplate: '/users_by_score',
            name: 'users by score',
            provider: UserProvider::class,
        ),
        new GetCollection(
            normalizationContext: ['groups' => 'user:collection:read'],
        ),
    ]
)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ApiProperty(identifier: false)]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty(identifier: true)]
    #[Groups(['user:collection:read', 'gif:read'])]
    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:collection:read', 'gif:read', 'user:read'])]
    private ?string $display_name = null;

    #[Groups(['user:read'])]
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $first_gif_time = null;

    #[Groups(['user:read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $last_gif_time = null;

    #[Groups(['user:collection:read', 'user:read'])]
    #[ORM\Column(nullable: true)]
    private ?int $count_gifs = null;

    #[ORM\OneToMany(mappedBy: 'first_use_user', targetEntity: Stat::class)]
    private Collection $originalGifs;

    public function __construct()
    {
        $this->originalGifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->display_name;
    }

    public function setDisplayName(?string $display_name): static
    {
        $this->display_name = $display_name;

        return $this;
    }

    public function getFirstGifTime(): ?\DateTimeImmutable
    {
        return $this->first_gif_time;
    }

    public function setFirstGifTime(?\DateTimeImmutable $first_gif_time): static
    {
        $this->first_gif_time = $first_gif_time;

        return $this;
    }

    public function getLastGifTime(): ?\DateTimeInterface
    {
        return $this->last_gif_time;
    }

    public function setLastGifTime(?\DateTimeInterface $last_gif_time): static
    {
        $this->last_gif_time = $last_gif_time;

        return $this;
    }

    public function getCountGifs(): ?int
    {
        return $this->count_gifs;
    }

    public function setCountGifs(?int $count_gifs): static
    {
        $this->count_gifs = $count_gifs;

        return $this;
    }

    public function getOriginalGifs(): Collection
    {
        return $this->originalGifs;
    }

    #[Groups(['user:collection:read', 'user:read'])]
    public function getCountOriginalGifs(): int
    {
        return $this->getOriginalGifs()->count();
    }
/*
    #[Groups(['user:read'])]
    // OSEF en fait
    public function getFirstGif(): Gif
    {
        $originalGifs = $this->getOriginalGifs()->toArray();
        usort($originalGifs, function ($a, $b) {
            if ($b->getFirstUse() === $a->getFirstUse()) {
                return $b->getId() < $a->getId(); // parce que évidement les premiers gifs peuvent être sur le même post
            }
            return $b->getFirstUse() < $a->getFirstUse();
        });
        return $originalGifs[0]->getGif();
    }
*/
}
