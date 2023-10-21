<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

final class TagProvider implements ProviderInterface
{
    public function __construct(
        private readonly ProviderInterface $itemProvider,
        private readonly EntityManagerInterface $em,
        private readonly Pagination $pagination,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            $itemsPerPage = $this->pagination->getLimit($operation, $context);
            $offset = $this->pagination->getOffset($operation, $context);
            $tags = $this->em->getRepository(Tag::class)->getTopTags($offset, $itemsPerPage);
            return new TraversablePaginator(
                new \ArrayIterator($tags),
                $this->pagination->getPage($context),
                $itemsPerPage,
                $this->em->getRepository(Tag::class)->count([]),
            );
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}