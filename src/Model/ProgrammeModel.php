<?php

declare(strict_types=1);

namespace Liszted\Model;

class ProgrammeModel
{
    /** @var list<ContributerModel> */
    public array $contributers = [];

    /** @var list<string> */
    public array $roles = [];

    /** @var list<string> */
    public array $dates = [];

    /** @var list<PerformanceModel> */
    public array $performances = [];

    /** @var list<WorkModel> */
    public array $works = [];

    /** @var array<int, VenueModel> */
    public array $venues = [];

    /** @var list<string> */
    public array $companies = [];

    public ?int $id = null;
    public ?int $date_accuracy = null;
    public ?int $show_works = null;
    public ?string $url = null;

    /** @var list<string> */
    public array $performance_urls = [];

    public ?string $description = null;
    public ?string $title = null;

    public function __construct(?self $toCopy = null)
    {
        if ($toCopy !== null) {
            $this->contributers = $toCopy->contributers;
            $this->dates = $toCopy->dates;
            $this->works = $toCopy->works;
            $this->venues = $toCopy->venues;
            $this->roles = $toCopy->roles;
            $this->companies = $toCopy->companies;
            $this->id = $toCopy->id;
            $this->url = $toCopy->url;
            $this->performance_urls = $toCopy->performance_urls;
            $this->performances = $toCopy->performances;
            $this->date_accuracy = $toCopy->date_accuracy;
            $this->show_works = $toCopy->show_works;
            $this->title = $toCopy->title;
            $this->description = $toCopy->description;
        }
    }
}
