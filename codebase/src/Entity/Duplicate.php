<?php

namespace App\Entity;

use App\Repository\DuplicateRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DuplicateRepository::class)]
#[ORM\Table(name: 'dup_md5')]
class Duplicate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'duplicates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gif $gif = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gif:read'])]
    private ?string $src = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSrc(): ?string
    {
        return $this->src;
    }

    public function setSrc(string $src): static
    {
        $this->src = $src;

        return $this;
    }
}
