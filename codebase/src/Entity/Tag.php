<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\TagRepository;
use App\State\TagProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tags')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            paginationItemsPerPage: 100,
            provider: TagProvider::class,
        ),
        new Post(),
    ]
)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name:'val', length: 255)]
    #[Groups(['gif:read','tags:update:read'])]
    private ?string $tag = null;

    #[ORM\ManyToMany(targetEntity: Gif::class)]
    #[ORM\JoinTable(name: 'l_tag_gif')]
    private Collection $gifs;

    public function __construct()
    {
        $this->gifs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return Collection<int, Gif>
     */
    public function getGifs(): Collection
    {
        return $this->gifs;
    }
}
