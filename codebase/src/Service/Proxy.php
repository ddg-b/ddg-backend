<?php

namespace App\Service;

use function Symfony\Component\String\u;

class Proxy
{
    public function __construct(private readonly string $imgurProxy)
    {
    }

    public function getSrc(string $src): string
    {
        if (u($src)->containsAny('imgur.com')) {
            return preg_replace('/^https:\/\/([^\/]+)/', $this->imgurProxy, $src);
        } else {
            return $src;
        }
    }

    public function getImgurProxy(): string
    {
        return $this->imgurProxy;
    }
}