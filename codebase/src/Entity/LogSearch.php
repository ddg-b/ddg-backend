<?php

namespace App\Entity;

use App\Repository\LogSearchRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'log_search')]
#[ORM\Entity(repositoryClass: LogSearchRepository::class)]
class LogSearch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'date', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(name: 'ip', length: 255)]
    private ?string $visitor = null;

    #[ORM\Column(length: 255)]
    private ?string $search = null;

    #[ORM\Column]
    private ?int $count_res = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getVisitor(): ?string
    {
        return $this->visitor;
    }

    public function setVisitor(string $visitor): static
    {
        $this->visitor = $visitor;

        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(string $search): static
    {
        $this->search = $search;

        return $this;
    }

    public function getCountRes(): ?int
    {
        return $this->count_res;
    }

    public function setCountRes(int $count_res): static
    {
        $this->count_res = $count_res;

        return $this;
    }
}
