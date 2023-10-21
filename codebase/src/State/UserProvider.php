<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class UserProvider implements ProviderInterface
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
            $users = $this->em->getRepository(User::class)->getUsersByScore($offset, $itemsPerPage);
            return new TraversablePaginator(
                new \ArrayIterator($users),
                $this->pagination->getPage($context),
                $itemsPerPage,
                $this->em->getRepository(User::class)->count([]),
            );
        }

        return $this->itemProvider->provide($operation, $uriVariables, $context);
    }
}