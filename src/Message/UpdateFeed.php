<?php

namespace Crell\Bundle\Planedo\Message;

final class UpdateFeed
{
    public function __construct(
        public int $feedId,
    ) {}
}
