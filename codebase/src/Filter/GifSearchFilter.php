<?php

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\LogSearch;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class GifSearchFilter extends AbstractFilter
{

    public function __construct(
        ManagerRegistry $managerRegistry,
        LoggerInterface $logger = null,
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null,
    )
    {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void
    {

        if ($property !== 'search') {
            return;
        }
        $property = 'tags.tag';

        $alias = $queryBuilder->getRootAliases()[0];
        $valueParameter = $queryNameGenerator->generateParameterName($property);
        if (3 > strlen($value)) {
            $queryBuilder
                ->andWhere('true = false');
        } else {
            $queryBuilder->innerJoin($alias.'.tags', 't')
                ->andWhere('t.tag like :'.$valueParameter)
                ->setParameter($valueParameter, '%'.$value.'%');
        }
    }

    public function apply(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        parent::apply($queryBuilder, $queryNameGenerator, $resourceClass, $operation, $context);

        $paginator = new Paginator($queryBuilder);
        $this->requestStack->getCurrentRequest()->getClientIp();
        $logSearch = (new LogSearch())
            ->setDate(new \DateTimeImmutable())
            ->setVisitor(sha1($this->requestStack->getCurrentRequest()->getClientIp()))
            ->setSearch($context['filters']['search'] ?? 'UNKNOWN')
            ->setCountRes($paginator->count())
        ;
        $this->em->persist($logSearch);
        $this->em->flush();
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => null,
                'type' => 'string',
                'required' => false,
                'swagger' => ['description' => ''],
            ],
        ];
    }
}
