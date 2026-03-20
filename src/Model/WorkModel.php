<?php

declare(strict_types=1);

namespace Liszted\Model;

class WorkModel
{
    public ?string $name = null;

    /** @var list<ContributerModel> */
    public array $contributers = [];

    public ?string $url = null;
}
