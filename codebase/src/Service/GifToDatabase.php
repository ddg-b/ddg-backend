<?php

namespace App\Service;

use App\Entity\Crawler;
use App\Entity\Duplicate;
use App\Entity\Gif;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\u;

class GifToDatabase
{
    private const VALID_EXTENSIONS = [
        'gif',
        'mp4',
        'webm',
    ];

    private const FILES = [
        '404' => 'res_404.txt'
    ];

    private const GIFS_DIR = 'gifs';

    public function __construct(
        private readonly string $resources_dir,
        private readonly string $thumbs_dir,
        private readonly Image $image,
        private readonly Proxy $proxy,
        private readonly FFMpeg $ffmpeg,
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $httpClient,
        private readonly Filesystem $filesystem,
    )
    {
    }

    public function addToDatabase(): void
    {
        $this->checkFiles();
        /** @var Crawler[] $newCrawledGifs */
        $newCrawledGifs = $this->em->getRepository(Crawler::class)->findBy(['new' => 1]);
        $gifs_404 = explode(PHP_EOL, file_get_contents($this->resources_dir.'/'.self::FILES['404']));
        $all_src_from_database = $this->getAllSrcFromDatabase();

        foreach ($newCrawledGifs as $k => $newCrawledGif) {
            $src = $newCrawledGif->getSrc();
            echo PHP_EOL.'Processing ('.($k+1).'/'.count($newCrawledGifs).') '.$src;

            $src = $this->proxy->getSrc($src);
            $extension = pathinfo($src, \PATHINFO_EXTENSION);
            if (u($src)->length() > 255) {
                echo PHP_EOL.'src too long : '.$src;

                continue;
            }
            if (!in_array($extension,self::VALID_EXTENSIONS, true)) {
                echo PHP_EOL.'invalid extension : '.$src;

                continue;
            }
            if (in_array($src, $gifs_404) || in_array($src, $all_src_from_database)) {

                continue;
            }
            try {
                $f = $this->httpClient->request('GET', $src, ['timeout' => 2.5])->getContent();
            } catch (TransportExceptionInterface $e) {
                $this->filesystem->appendToFile('res_404.txt', $src);
                echo PHP_EOL.'not reachable : '.$src;

                continue;
            }
            $md5 = \md5($f);
            $existingGif = $this->em->getRepository(Gif::class)->findOneBy(['hash' => $md5]);
            if ($existingGif instanceof Gif) {
                $this->saveDuplicated($existingGif, $src);

                continue;
            }
            $filename = $this->resources_dir.'/'.self::GIFS_DIR.'/'.$md5.'.'.$extension;
            $this->filesystem->dumpFile($filename, $f);
            $filesize = \filesize($filename);
            if ('gif' === $extension) {
                try {
                    list('width' => $width, 'height' => $height) = $this->image->imagethumb($filename, $this->thumbs_dir, $md5.'.jpg');

                } catch (Exception) {
                    $this->filesystem->remove($filename);
                    echo PHP_EOL.'cannot create thumb : '.$src;

                    continue;
                }
            } else {
                shell_exec('ffmpeg -i '.$filename.' -vf "select=eq(n\,0)" -vf scale=300:-2 -q:v 3  '.$this->thumbs_dir.'/'.$md5.'.jpg');
                $this->ffmpeg->load($filename);
                list('width' => $width, 'height' => $height) = $this->ffmpeg->get_infos();
            }
            $this->saveGif($src, $md5, $extension, $width, $height, $filesize);
            // avoid inserting a duplicate in the same batch
            $all_src_from_database[] = $src;
            echo PHP_EOL.'added : '.$src;
        }
        $this->flagAsNotNew($newCrawledGifs);
    }

    private function checkFiles(): void
    {
        foreach (self::FILES as $file) {
            if (!$this->filesystem->exists($this->resources_dir.'/'.$file)) {
                $this->filesystem->touch($this->resources_dir.'/'.$file);
            }
        }
    }

    private function getAllSrcFromDatabase(): array
    {
        return array_merge(
            $this->em->getRepository(Gif::class)->getAllSrc(),
            $this->em->getRepository(Duplicate::class)->getAllSrc()
        );
    }

    private function saveDuplicated($existingGif, $src): void
    {
        $duplicated = (new Duplicate())
            ->setGif($existingGif)
            ->setSrc($src)
        ;
        $this->em->persist($duplicated);
        $this->em->flush();
    }

    private function saveGif(string $src, string $md5, string $extension, int $width, int $height, int $size): void
    {
        $gif = (new Gif)
            ->setSrc($src)
            ->setHash($md5)
            ->setExtension($extension)
            ->setWidth($width)
            ->setHeight($height)
            ->setSize($size)
            ->setCreateDate(new \DateTimeImmutable())
        ;
        $this->em->persist($gif);
        $this->em->flush();
    }

    private function flagAsNotNew(array $newCrawledGifs): void
    {
        if (0 < count($newCrawledGifs)) {
            foreach ($newCrawledGifs as $newCrawledGif) {
                $newCrawledGif->setNew(false);
                $this->em->persist($newCrawledGif);
            }
            $this->em->flush();
        }
    }
}