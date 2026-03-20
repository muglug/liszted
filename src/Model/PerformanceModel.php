<?php

declare(strict_types=1);

namespace Liszted\Model;

class PerformanceModel
{
    public ?int $id = null;
    public ?VenueModel $venue = null;
    public ?string $start = null;
    public ?string $end = null;
    public ?string $url = null;
    public ?int $accuracy = null;
}
