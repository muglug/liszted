<?php

declare(strict_types=1);

namespace Liszted\Controller;

use Liszted\Model\CalendarModel;
use Liszted\Model\ProgrammeModel;

class Calendar
{
    /**
     * @param array<string, string> $calendarData
     */
    public static function monthly(array $calendarData, ?int $month = null, ?int $year = null): string
    {
        $month = $month ?? (int) date("m");
        $year = $year ?? (int) date("Y");

        $monthStart = mktime(0, 0, 0, $month, 1, $year);
        $monthEnd = mktime(0, 0, 0, $month + 1, 0, $year);

        $startDate = $monthStart - (int) date("w", $monthStart) * 86400;
        $endDate = $monthEnd + (7 - (int) date("w", $monthEnd)) * 86400;

        ksort($calendarData);

        $calendarText = "<div class=\"month\"><div class=\"week\">";
        $numDays = (int) round(($endDate - $startDate) / 86400);

        for ($i = 0; $i < $numDays; $i++) {
            $currentDay = mktime(0, 0, 0, (int) date("m", $startDate), (int) date("d", $startDate) + $i, (int) date("Y", $startDate));
            $nextDay = mktime(0, 0, 0, (int) date("m", $startDate), (int) date("d", $startDate) + $i + 1, (int) date("Y", $startDate));

            $ul = [];
            foreach ($calendarData as $date => $event) {
                $ts = strtotime($date);
                if ($ts !== false && $ts >= $currentDay && $ts < $nextDay) {
                    $ul[] = $event;
                }
            }
            $skipped = ($currentDay < $monthStart || $currentDay > $monthEnd) ? " skipped" : "";
            $calendarText .= "<div class=\"day{$skipped}\">";
            $calendarText .= "<h4><span>" . date("l", $currentDay) . "</span> " . date("M j", $currentDay) . "</h4>";

            if (count($ul) > 0) {
                $calendarText .= "<ul><li>" . implode("</li><li>", $ul) . "</li></ul>";
            }
            $calendarText .= "</div>";

            if ((int) date("w", $currentDay) === 6) {
                $calendarText .= "</div><div class=\"week\">";
            }
        }
        $calendarText .= "</div></div>";
        return $calendarText;
    }

    /**
     * @return array{CalendarModel, CalendarModel}
     */
    public static function groupedModels(CalendarModel $calendar, bool $splitVenues = false): array
    {
        $calendarGroupedDays = Util::groupCalendarDays($calendar->days, $splitVenues);

        $previous = new CalendarModel();
        $upcoming = new CalendarModel();

        foreach ($calendarGroupedDays as $cg) {
            $programme = new ProgrammeModel($cg[0]);
            $programme->dates = $cg[1];
            $performances = $programme->performances;
            $programme->performances = [];
            foreach ($performances as $performance) {
                if (in_array($performance->start, $programme->dates, true)) {
                    $programme->performances[] = $performance;
                }
            }
            $lastIdx = count($cg[1]) - 1;
            if ($lastIdx >= 0 && strtotime($cg[1][$lastIdx]) > time() - 86400) {
                $upcoming->performances[] = $programme;
            } else {
                $previous->performances[] = $programme;
            }
        }
        return [$upcoming, $previous];
    }

    /**
     * @return array{CalendarModel, CalendarModel}
     */
    public static function distinctModels(CalendarModel $calendar): array
    {
        $previous = new CalendarModel();
        $upcoming = new CalendarModel();

        foreach ($calendar->programmes as $programme) {
            if (count($programme->dates) > 0 && strtotime($programme->dates[count($programme->dates) - 1]) > time()) {
                $upcoming->performances[] = $programme;
            } else {
                $previous->performances[] = $programme;
            }
        }
        return [$upcoming, $previous];
    }
}
