<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Dto\TagsDto;
use App\Filter\GifSearchFilter;
use App\Repository\GifRepository;
use App\State\TagProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GifRepository::class)]
#[ORM\Table(name: 'gifs')]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: 'gifs/{id}',
            normalizationContext: ['groups' => ['gif:read']],
        ),
        new GetCollection(
            uriTemplate: 'gifs',
            normalizationContext: ['groups' => ['gif:read']],
        ),
        new Put(
            uriTemplate: 'gifs/{id}/tags',
            normalizationContext: ['groups' => ['tags:update:read']],
            denormalizationContext: ['groups' => ['tags:update']],
            input: TagsDto::class,
            processor: TagProcessor::class
        )
    ]
)]
#[ApiFilter(OrderFilter::class, properties: ['stats.count_used', 'stats.last_use', 'stats.first_use'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(SearchFilter::class, properties: ['stats.first_use_user.username' => 'exact', 'tags.tag' => 'exact'])]
#[ApiFilter(GifSearchFilter::class)]
class Gif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['gif:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['gif:read'])]
    private ?string $src = null;

    #[ORM\Column(name: 'date', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeInterface $create_date = null;

    #[ORM\Column(name: 'md5', length: 255)]
    #[Groups(['gif:read'])]
    private ?string $hash = null;

    #[ORM\Column(name: 'ext', length: 255, nullable: true)]
    private ?string $extension = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['gif:read'])]
    private ?int $width = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['gif:read'])]
    private ?int $height = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['gif:read'])]
    private ?int $size = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $update_date = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\JoinTable(name: 'l_tag_gif')]
    #[ORM\JoinColumn(name: 'gif_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[Groups(['gif:read', 'tags:update:read'])]
    private Collection $tags;

    #[ORM\OneToOne(mappedBy: 'gif', targetEntity: Stat::class, orphanRemoval: true)]
    #[Groups(['gif:read'])]
    private Stat $stats;

    #[ORM\OneToMany(mappedBy: 'gif', targetEntity: Duplicate::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['gif:read'])]
    private Collection $duplicates;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->duplicates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->update_date;
    }

    public function setUpdateDate(?\DateTimeInterface $update_date): static
    {
        $this->update_date = $update_date;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): static
    {
        $this->hash = $hash;

        return $this;
    }

    public function getCreateDate(): \DateTimeInterface
    {
        return $this->create_date;
    }

    public function setCreateDate(\DateTimeImmutable $create_date): static
    {
        $this->create_date = $create_date;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getStats(): ?Stat
    {
        return $this->stats;
    }

    public function setStats(?Stat $stats): static
    {
        // unset the owning side of the relation if necessary
        if ($stats === null && $this->stats !== null) {
            $this->stats->setGif(null);
        }

        // set the owning side of the relation if necessary
        if ($stats !== null && $stats->getGif() !== $this) {
            $stats->setGif($this);
        }

        $this->stats = $stats;

        return $this;
    }

    /**
     * @return Collection<int, Duplicate>
     */
    public function getDuplicates(): Collection
    {
        return $this->duplicates;
    }

    public function addDuplicate(Duplicate $duplicate): static
    {
        if (!$this->duplicates->contains($duplicate)) {
            $this->duplicates->add($duplicate);
            $duplicate->setGif($this);
        }

        return $this;
    }

    public function removeDuplicate(Duplicate $duplicate): static
    {
        if ($this->duplicates->removeElement($duplicate)) {
            // set the owning side to null (unless already changed)
            if ($duplicate->getGif() === $this) {
                $duplicate->setGif(null);
            }
        }

        return $this;
    }
}
