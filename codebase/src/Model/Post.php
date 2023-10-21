<?php

namespace App\Model;

class Post
{
    private ?string $pseudo = null;

    private ?\DateTimeImmutable $date = null;

    private array $srcSet = [];

    private int $topic;

    private ?int $page;

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
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

    public function getSrcSet(): array
    {
        return $this->srcSet;
    }

    public function setSrcSet(array $srcSet): static
    {
        $this->srcSet = $srcSet;

        return $this;
    }

    public function getTopic(): int
    {
        return $this->topic;
    }

    public function setTopic(int $topic): static
    {
        $this->topic = $topic;

        return $this;
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
}
