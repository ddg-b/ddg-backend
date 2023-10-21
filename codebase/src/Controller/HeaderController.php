<?php

namespace App\Controller;

use App\Entity\Gif;
use App\Entity\LogSearch;
use App\Entity\Stat;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class HeaderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    #[Route(
        path: '/api/header',
        name: 'api_header',
        defaults: [
            '_api_operation_name' => '_api_/header',
        ],
        methods: ['GET'],
    )]
    public function __invoke()
    {
        /** @var Stat $lastOriginalGif */
        $lastOriginalGif = $this->em->getRepository(Stat::class)->getLastOriginalGif();
        $out = [];
        $out['username'] = $lastOriginalGif->getFirstUseUser()->getUsername();
        $out['display_name'] = $lastOriginalGif->getFirstUseUser()->getDisplayName();
        $out['first_use'] = $lastOriginalGif->getFirstUse()->format(\DateTime::ATOM);
        $out['count_gifs'] = $this->em->getRepository(Gif::class)->count([]);
        $out['count_tags'] = $this->em->getRepository(Tag::class)->count([]);
        $out['count_search'] = $this->em->getRepository(LogSearch::class)->count([]);

        return new JsonResponse($out);
    }
}