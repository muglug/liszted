<?php

declare(strict_types=1);

namespace Liszted\Controller;

class Parser
{
    /**
     * @return array{string, string, string} [city, state, country]
     */
    public static function parsePlace(string $place): array
    {
        Constants::init();
        $country = "US";
        if (str_contains($place, " USA")) {
            $place = trim(str_replace(" USA", "", $place));
        }
        $parts = explode(",", $place);
        $city = array_shift($parts);
        foreach (Constants::$countries as $k => $c) {
            if (strtolower($c) === strtolower($city)) {
                $country = $k;
            }
        }
        $state = "";
        foreach ($parts as $part) {
            $part = strtolower(trim($part));
            foreach (Constants::$countries as $k => $c) {
                if (strtolower($c) === $part) {
                    $country = $k;
                }
            }
            foreach (Constants::$us_states as $k => $s) {
                if (strtolower($s) === $part || strtolower($k) === $part) {
                    $state = $k;
                }
            }
        }
        return [$city, $state, $country];
    }

    /**
     * @return list<int>
     */
    public static function parseDates(string $dateString): array
    {
        $dates = [];
        $years = [];
        preg_match("/(\d{4})/", $dateString, $years);
        $years = array_unique($years);
        if (count($years) === 1) {
            $dateString = str_replace(" and ", " & ", $dateString);
            $dateString = preg_replace("/ " . $years[0] . "/", "", $dateString) ?? $dateString;
            $monthRegex = "/(((" . implode("|", Constants::$months) . ") ((\d{1,2}m?(, | & )?)+)))/";
            $monthMatches = preg_split($monthRegex, $dateString, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
            $monthStrings = [];
            foreach ($monthMatches as $match) {
                if (preg_match($monthRegex, $match)) {
                    $monthStrings[] = trim(preg_replace("/(,\s?|\s?&\s)$/", "", $match) ?? $match);
                }
            }
            $monthStrings = array_unique($monthStrings);
            foreach ($monthStrings as $monthString) {
                $sb = explode(" ", $monthString);
                $month = array_shift($sb);
                $days = preg_split("/\s?[,&]\s?/", implode(" ", $sb)) ?: [];
                $monthIndex = 0;
                foreach (Constants::$months as $k => $m) {
                    if ($m === $month) {
                        $monthIndex = $k + 1;
                    }
                }
                foreach ($days as $day) {
                    $day = str_replace("m", "", $day);
                    $dates[] = mktime(0, 0, 0, $monthIndex, (int) $day, (int) $years[0]);
                }
            }
        }
        return $dates;
    }

    /**
     * @param list<string> $replacementParts
     */
    public static function fillIn(string $date, array $replacementParts): string
    {
        $newDateParts = explode(" ", $date);
        foreach ($replacementParts as $k => $p) {
            if (!isset($newDateParts[$k])) {
                $newDateParts[$k] = $p;
            }
        }
        return implode(" ", $newDateParts);
    }
}
