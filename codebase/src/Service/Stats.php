<?php

namespace App\Service;

use App\Entity\Crawler;
use App\Entity\Duplicate;
use App\Entity\Gif;
use App\Entity\Stat;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use function Symfony\Component\String\u;

class Stats
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public function run(): void
    {
        $crawled_gifs_for_stats = $this->em->getRepository(Crawler::class)->findBy(['stats' => 0]);
        foreach ($crawled_gifs_for_stats as $crawled) {
            if (null === ($user = $this->getUserByUsername($crawled->getPseudo()))) {
                $user = $this->addUser($crawled->getPseudo(), $crawled->getDate());
            }
            $gif = $this->getGifBySrc($crawled->getSrc());
            $this->saveStats($gif, $user, $crawled);
        }
    }

    private function getUserByUsername(string $username): ?User
    {
        $username = u($username)->lower()->toString();

        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    private function addUser(string $display_name, $crawled_date): User
    {
        $username = u($display_name)->lower()->toString();
        $user = (new User())
            ->setUsername($username)
            ->setDisplayName($display_name)
            ->setFirstGifTime($crawled_date)
            ->setLastGifTime($crawled_date)
            ->setCountGifs(0)
        ;
        $this->em->persist($user);
        $this->em->flush();
        $this->em->refresh($user);

        return $user;
    }

    private function getGifBySrc(string $src): ?Gif
    {
        $gif = $this->em->getRepository(Gif::class)->findOneBy(['src' => $src]);
        if (null !== $gif) {

            return $gif;
        }
        $duplicate = $this->em->getRepository(Duplicate::class)->findOneBy(['src' => $src]);

        return $duplicate?->getGif();
    }

    private function saveStats(Gif $gif, User $user, Crawler $crawled): void
    {
        $existing_stats = $this->em->getRepository(Stat::class)->findOneBy(['gif' => $gif]);

        if (null !== $existing_stats) {
            $existing_stats
                ->setLastUse($crawled->getDate())
                ->setLastUseUser($user)
                ->setCountUsed($existing_stats->getCountUsed() + 1)
            ;
            $this->em->persist($existing_stats);
        } else {
            $new_stats = (new Stat())
                ->setGif($gif)
                ->setFirstUse($crawled->getDate())
                ->setFirstUseUser($user)
                ->setLastUse($crawled->getDate())
                ->setLastUseUser($user)
                ->setCountUsed(1)
            ;
            $this->em->persist($new_stats);
        }
        $user
            ->setCountGifs($user->getCountGifs() + 1)
            ->setLastGifTime($crawled->getDate())
            ->setDisplayName($crawled->getPseudo())
        ;
        $this->em->persist($user);
        $crawled
            ->setUser($user)
            ->setGif($gif)
            ->setStats(1)
        ;
        $this->em->persist($crawled);

        $this->em->flush();
    }
}