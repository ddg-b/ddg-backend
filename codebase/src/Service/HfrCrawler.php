<?php

namespace App\Service;

use App\Entity\Crawler;
use App\Model\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\u;

class HfrCrawler
{
    private const PAGE = 'PAGE';
    private const CONFIG = [
        [
            'topic_id' => 1,
            'topic_url' => '/forum2.php?config=hfr.inc&cat=13&subcat=434&post=97959&page='.self::PAGE.'&p=1&sondage=0&owntopic=1&trash=0&trash_post=0&print=0&numreponse=0&quote_only=0&new=0&nojs=0'
        ],
    ];
    /**
     * @var Collection<int, Post>
     */
    private Collection $posts;
    private ?Post $oldestPost = null;
    private ?Post $newestPost = null;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HttpClientInterface $hfrClient,
        private readonly string $hfrForumImage,
    )
    {
        $this->posts = new ArrayCollection();
    }

    public function addCrawledPosts(): void
    {
        if (!$this->posts->isEmpty()) {
            $this->findMinMaxDate();
            $existingCrawled = $this->em->getRepository(Crawler::class)
                ->getExistingCrawledByDate(
                    $this->oldestPost->getDate(),
                    $this->newestPost->getDate(),
                    $this->newestPost->getTopic()
                );
            $crawledComp = [];
            foreach ($existingCrawled as $cr) {
                //on ne met pas $cr['page'] en cas de delete
                $crawledComp[] = $cr->getDate()->format(\DATE_ATOM).'|'.strtolower($cr->getPseudo()).'|'.$cr->getSrc();
            }
            foreach ($this->posts as $post) {
                foreach ($post->getSrcSet() as $src) {
                    $comp = $post->getDate()->format(\DATE_ATOM).'|'.strtolower($post->getPseudo()).'|'.$src;
                    if (!in_array($comp, $crawledComp)) {
                        $crawler = (new Crawler())
                            ->setPage($post->getPage())
                            ->setDate($post->getDate())
                            ->setPseudo($post->getPseudo())
                            ->setSrc($src)
                            ->setOrigin($post->getTopic())
                        ;
                        $this->em->persist($crawler);
                        $this->em->flush();
                        echo PHP_EOL.'Crawler: Ajouté : '.$comp;
                    } else {
                        echo PHP_EOL."Existe deja en db : ".$comp;
                    }
                }
            }
        }
    }

    public function crawl(): static
    {
        foreach (self::CONFIG as $config) {
            $this->crawlByConfig($config);
        }

        return $this;
    }

    private function crawlByConfig(array $config): void
    {
        $current_page = $config['forced_current_page']
            ?? $this->em->getRepository(Crawler::class)->getCurrentPage($config['topic_id']);
        $pages = [$current_page - 1,$current_page,$current_page + 1]; // -1 => delete, +1 => next page
        foreach ($pages as $page) {
            $this->crawlPage($page, $config['topic_id'], $config['topic_url']);
        }
    }

    private function crawlPage(int $page, int $topic_id, string $url): void
    {
        echo '.';
        $url = u($url)->replace(self::PAGE, (string) $page);
        $page_content = $this->hfrClient->request('GET', $url)->getContent();
        $start_tag = '<table cellspacing="0" cellpadding="4"';
        $end_tag = '</tr></table>';
        $split_tag = $end_tag.$start_tag;
        $tables = explode($split_tag, $page_content);

        unset($tables[0]);
        unset($tables[count($tables)]);
        
        foreach ($tables as $k => &$t) {
            $t = $start_tag.$t.$end_tag;
            if (strpos($t, '<b class="s2">Publicité</b>')) {
                unset($tables[$k]);
            }
        }
        foreach ($tables as $table) {
            $table = preg_replace('(&[0-9a-zA-Z]{0,6};)', '', $table);
            $table = preg_replace('/[\x{200b}]+/u', '', $table); // dans 'Profil supprimé'
            $table = str_replace('>', '>'.PHP_EOL, $table);
            $xml = simplexml_load_string($table, 'SimpleXMLElement', LIBXML_NOERROR);
            if (false === $xml) {
                echo PHP_EOL.'Petite erreur Page '.$page;
            } else {
                $pseudo = trim($xml->tr[0]->td[0]->div[1]->b);
                // $dateString = ltrim(str_replace('à', ' ', trim((string) $xml->tr[0]->td[1]->div[0]->div[0])), 'Posté le ');
                $dateString = substr(str_replace('à', ' ', trim((string) $xml->tr[0]->td[1]->div[0]->div[0])), 10);
                $date = \DateTimeImmutable::createFromFormat('d-m-Y H:i:s', $dateString);
                $srcSet = $this->extractGifsAndWebms($table);

                if (!empty($srcSet)) {
                    $post = (new Post())
                        ->setPseudo($pseudo)
                        ->setDate($date)
                        ->setSrcSet($srcSet)
                        ->setTopic($topic_id)
                        ->setPage($page)
                    ;
                    $this->posts->add($post);
                }
            }
        }
        sleep(1); // chill
    }

    private function extractGifsAndWebms($table): array
    {
        //gifs
        $start_tag_image = '<img';
        $end_tag_image = '>';
        $src = [];
        preg_match_all('/'.preg_quote($start_tag_image, '/').'(.*?)'.preg_quote($end_tag_image, '/').'/', $table, $images);
        if (!empty($images)) {
            foreach ($images[0] as $image) {
                $x = simplexml_load_string($image);
                if (
                    !u($x['src'])->containsAny($this->hfrForumImage) &&
                    u($x['src'])->endsWith('.gif') &&
                    !in_array((string) $x['src'], $src)
                ) {
                    $src[] = (string) $x['src'];
                }
            }
        }

        //webms
        $start_tag_image = '<a';
        preg_match_all('/'.preg_quote($start_tag_image, '/').'(.*?)'.preg_quote($end_tag_image, '/').'/', $table, $images);

        if (!empty($images)) {
            foreach ($images[0] as $image) {
                $image = u($image)->replace('>', '/>'); // pour avoir un xml valide
                $x = simplexml_load_string($image);
                $href = (string) $x['href'];
                if (
                    (u($href)->containsAny('.mp4') || u($href)->containsAny('.webm')) &&
                    !in_array($href, $src)
                ) {
                    $src[] = $href;
                }
            }
        }
        return $src;
    }

    private function findMinMaxDate(): void
    {
        $this->oldestPost = $this->posts->first();
        $this->newestPost = $this->posts->last();

        $this->posts->forAll(function ($key, Post $post) {
            if ($post->getDate() < $this->oldestPost->getDate()) {
                $this->oldestPost = $post;
            }
            if ($post->getDate() > $this->newestPost->getDate()) {
                $this->newestPost = $post;
            }
            return true;
        });
    }
}
