<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class TagsDto
{
    #[Assert\Regex(
        pattern: '/<[^>]*>/',
        message: 'The tag is invalid',
        match: false,
    )]
    #[Groups(['tags:update'])]
    public ?string $tags = null;
}