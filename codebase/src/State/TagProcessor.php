<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Gif;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\AbstractString;
use function Symfony\Component\String\u;

class TagProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @template T
     * @return T
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $gif = $this->em->getRepository(Gif::class)->find($uriVariables['id']);

        if (empty($data->tags)) {
            return $gif;
        }

        $oldTags = $gif->getTags();
        if (!$oldTags->isEmpty()) {
            foreach ($oldTags as $oldTag) {
                $gif->removeTag($oldTag);
            }
        }
        $separator = u($data->tags)->match('/[,;]/u')[0] ?? null;
        $tags = null === $separator
            ? [$data->tags]
            : array_map(fn (AbstractString $tag) => $tag->trim()->toString(), u($data->tags)->split($separator));

        foreach ($tags as $tag) {
            $existingTag = $this->em->getRepository(Tag::class)->findOneBy(['tag' => $tag]);
            if (!$existingTag instanceof Tag) {
                $newTag = (new Tag())->setTag($tag);
                $gif->addTag($newTag);
                $this->em->persist($newTag);
            } else {
                $gif->addTag($existingTag);
            }
            $this->em->persist($gif);
        }

        return $this->persistProcessor->process($gif, $operation, $uriVariables, $context);
    }
}
