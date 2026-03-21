<?php

declare(strict_types=1);

error_reporting(E_ALL);
ignore_user_abort(true);

require_once dirname(__DIR__, 2) . '/src/bootstrap.php';

use Liszted\Controller\Constants;
use Liszted\Controller\Search;
use Liszted\Controller\Updater;
use Liszted\Database\Connection;

Connection::execute("DELETE FROM series WHERE user = ?", [14]);

$source = file_get_contents("https://thomasades.com/performances/export");

$calendar = json_decode($source, true);

$ades_id = Updater::contributer("Thomas", "Adès");
$composer_role = Updater::role("Composer");

$ades_performance_ids = [];

$performance_search = Connection::fetchAll(
    "SELECT id FROM performance WHERE programme IN (SELECT programme FROM programme_line WHERE programme_line.id IN (SELECT programme_line FROM contribution WHERE contributer = ?))",
    [$ades_id]
);
foreach ($performance_search as $ps) {
    $ades_performance_ids[] = $ps["id"];
}

$country_lookup = array_flip(Constants::$countries);
$country_lookup["USA"] = "US";

foreach ($calendar as $programme) {
    $series_id = Connection::upsert(0, "series", ["name" => "", "user" => 14]);

    $programme_id = Connection::upsert(0, "programme", ["series" => $series_id]);

    foreach ($programme[1] as $work) {
        $pl_id = Connection::upsert(0, "programme_line", [
            "text" => html_entity_decode($work[0], ENT_COMPAT, "UTF-8"),
            "programme" => $programme_id,
            "url" => "https://thomasades.com/compositions/" . $work[1],
        ]);
        Connection::upsert(0, "contribution", [
            "contributer" => $ades_id,
            "programme_line" => $pl_id,
            "role" => $composer_role,
        ]);
    }

    foreach ($programme[0] as $contributer) {
        $contributer = trim(str_replace("  ", " ", html_entity_decode($contributer, ENT_COMPAT, "UTF-8")));
        $contributer_id = Updater::contributer($contributer);
        Connection::upsert(0, "contribution", [
            "contributer" => $contributer_id,
            "programme" => $programme_id,
        ]);
    }

    $dates = [];

    foreach ($programme[2] as $engagement) {
        [$location, $dates] = $engagement;

        $date_search = [];
        $date_params = [];
        foreach ($dates as $date) {
            $date_search[] = "(start >= ? AND start <= ?)";
            $date_params[] = $date . " 00:00:00";
            $date_params[] = $date . " 23:59:00";
        }

        $duplicate_query = "SELECT COUNT(*) AS c FROM performance
JOIN programme ON programme.id = performance.programme
JOIN series s ON s.id = programme.series
WHERE s.user = 16
AND (" . implode(" OR ", $date_search) . ") AND performance.id IN (SELECT id FROM performance WHERE programme IN (SELECT programme FROM programme_line WHERE programme_line.id IN (SELECT programme_line FROM contribution WHERE contributer = ?)))";
        $date_params[] = $ades_id;

        $c = Connection::fetch($duplicate_query, $date_params);
        if ((int) $c["c"] !== count($dates)) {
            $country = $country_lookup[$location[3]] ?? $location[3];

            $city_name = trim(str_replace("  ", " ", html_entity_decode($location[1], ENT_COMPAT, "UTF-8")));
            $state_name = trim(str_replace("  ", " ", html_entity_decode($location[2], ENT_COMPAT, "UTF-8")));

            $city_id = Updater::city($city_name, $state_name, $country);

            $clean_venue_name = trim(str_replace("  ", " ", html_entity_decode($location[0], ENT_COMPAT, "UTF-8")));

            $venue_id = Updater::venue($clean_venue_name, (int) $city_id);

            foreach ($dates as $date) {
                $inner_dupes = 0;
                if ((int) $c["c"] > 0) {
                    $duplicate_date_query = "SELECT COUNT(*) AS c FROM performance
JOIN programme ON programme.id = performance.programme
JOIN series s ON s.id = programme.series
WHERE s.user = 16
AND (start >= ? AND start <= ?) AND performance.id IN (SELECT id FROM performance WHERE programme IN (SELECT programme FROM programme_line WHERE programme_line.id IN (SELECT programme_line FROM contribution WHERE contributer = ?)))";

                    $d = Connection::fetch($duplicate_date_query, [$date . " 00:00:00", $date . " 23:59:00", $ades_id]);
                    $inner_dupes = (int) $d["c"];
                }
                if ($inner_dupes === 0) {
                    Connection::upsert(0, "performance", [
                        "programme" => $programme_id,
                        "start" => $date,
                        "venue" => $venue_id,
                    ]);
                }
            }
        }
    }

    echo "date: " . implode("\n", $dates) . "\n";
}
