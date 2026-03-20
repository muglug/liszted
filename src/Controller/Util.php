<?php

declare(strict_types=1);

namespace Liszted\Controller;

class Util
{
    /**
     * @param list<string> $pieces
     */
    public static function join(array $pieces, bool $addBreaks = false): string
    {
        if (empty($pieces)) {
            return "";
        }
        if (count($pieces) === 1) {
            return $pieces[0];
        }
        $last = array_pop($pieces);
        $br = $addBreaks ? "<br />" : " ";
        return implode(",{$br}", $pieces) . " &amp;{$br}" . $last;
    }

    /**
     * @param list<string> $dates
     */
    public static function simplifyDates(array $dates, bool $addBreaks = false, ?int $accuracy = null): string
    {
        $fd = [];
        foreach ($dates as $date) {
            $dateParts = explode(" ", $date);
            $dayPart = array_shift($dateParts);
            $dp = explode("-", $dayPart);
            if (!isset($fd[$dp[0]])) {
                $fd[$dp[0]] = [];
            }
            if (!isset($fd[$dp[0]][$dp[1]])) {
                $fd[$dp[0]][$dp[1]] = [];
            }
            $day = ((int) $dp[2]);
            if ($day > 0) {
                $fd[$dp[0]][$dp[1]][] = $day;
            }
        }

        $yearText = [];
        foreach ($fd as $year => $months) {
            $monthText = [];
            if ($accuracy === null || $accuracy <= 2) {
                foreach ($months as $month => $days) {
                    $days = array_unique($days);
                    $dayText = "";
                    if ($accuracy === null || $accuracy <= 1) {
                        $newDays = [];
                        $firstDay = array_shift($days);
                        $nextDay = $firstDay;
                        $newDays[] = (string) $firstDay;
                        foreach ($days as $day) {
                            if ($day === $nextDay + 1) {
                                $allDays = explode("-", $newDays[count($newDays) - 1]);
                                $base = array_shift($allDays);
                                $newDays[count($newDays) - 1] = $base . "-" . $day;
                            } else {
                                $newDays[] = (string) $day;
                            }
                            $nextDay = $day;
                        }
                        $lastDay = "";
                        if (count($newDays) > 1) {
                            $lastDay = " &amp; " . array_pop($newDays);
                        }
                        $dayText = " " . implode(", ", $newDays) . $lastDay;
                    }
                    $monthText[] = date("F", mktime(0, 0, 0, (int) $month, 1, (int) $year)) . $dayText;
                }
            } elseif ($accuracy === 3) {
                foreach ($months as $month => $days) {
                    $monthText[] = Constants::$seasons[((int) $month) - 1];
                }
                $monthText = array_values(array_unique($monthText));
            }
            $br = $addBreaks ? "<br />" : "";
            $yearText[] = implode(", {$br}", $monthText) . " " . $year;
        }
        return implode(", ", $yearText);
    }

    /**
     * @param array<string, mixed> $calendar
     * @return list<array{mixed, list<string>}>
     */
    public static function groupCalendarDates(array $calendar): array
    {
        $oldDescription = "";
        $calendarGrouped = [];
        foreach ($calendar as $date => $description) {
            if ($oldDescription !== $description) {
                $calendarGrouped[] = [$description, [$date]];
            } else {
                $calendarGrouped[count($calendarGrouped) - 1][1][] = $date;
            }
            $oldDescription = $description;
        }
        return $calendarGrouped;
    }

    /**
     * @param array<string, list<\Liszted\Model\ProgrammeModel>> $calendarDays
     * @return list<array{\Liszted\Model\ProgrammeModel, list<string>}>
     */
    public static function groupCalendarDays(array $calendarDays, bool $splitVenues = false): array
    {
        $programmeIdOld = null;
        $venueIdOld = null;
        $calendarGrouped = [];
        foreach ($calendarDays as $date => $programmes) {
            foreach ($programmes as $programme) {
                $venueId = !empty($programme->venues) ? ($programme->venues[0]->id ?? null) : null;
                if ($programmeIdOld !== $programme->id || ($splitVenues && $venueId !== $venueIdOld)) {
                    $calendarGrouped[] = [$programme, [$date]];
                } else {
                    $calendarGrouped[count($calendarGrouped) - 1][1][] = $date;
                }
                $programmeIdOld = $programme->id;
                $venueIdOld = $venueId;
            }
        }
        return $calendarGrouped;
    }

    public static function verifyPassword(string $password, string $storedHash): bool
    {
        return password_verify($password, $storedHash);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
