<?php

declare(strict_types=1);

namespace Liszted\Model;

class CalendarModel
{
    /** @var list<ProgrammeModel> */
    public array $performances = [];

    /** @var list<ProgrammeModel> */
    public array $programmes = [];

    /** @var array<string, list<ProgrammeModel>> */
    public array $days = [];

    public ?string $title = null;
}
