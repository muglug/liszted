<?php

declare(strict_types=1);

namespace Liszted\Controller;

class Constants
{
    public static string $url_regex = "(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?]))";
    public static string $space_regex = "([\s\t]|\xC2\xA0)";

    /** @var list<string> */
    public static array $months = ["January","February","March","April","May","June","July","August","September","October","November","December"];

    /** @var list<string> */
    public static array $seasons = ["Winter","Winter","Spring","Spring","Spring","Summer","Summer","Summer","Autumn","Autumn","Autumn","Winter"];

    /** @var list<array{int, string}> */
    public static array $month_select = [[1,"January"],[2,"February"],[3,"March"],[4,"April"],[5,"May"],[6,"June"],[7,"July"],[8,"August"],[9,"September"],[10,"October"],[11,"November"],[12,"December"]];

    /** @var list<int> */
    public static array $year_select = [2010,2011,2012,2013,2014,2015,2016,2017,2018,2019,2020,2021,2022,2023,2024,2025,2026,2027,2028,2029,2030,2031,2032,2033,2034];

    /** @var list<int> */
    public static array $hour_select = [1,2,3,4,5,6,7,8,9,10,11,12];

    /** @var list<array{int, string}|int> */
    public static array $minute_select = [[0,"00"],[5,"05"],10,15,20,25,30,35,40,45,50,55];

    /** @var list<array{int, string}> */
    public static array $duration_select = [[15,"15 min"],[30,"30 min"],[45,"45 min"],[60,"1 hr"],[75,"1 hr 15 m"],[90,"1 hr 30 m"],[105,"1 hr 45 m"],[120,"2 hrs"],[150,"2 hrs 30 min"],[180,"3 hrs"],[210,"3 hrs 30 min"],[240,"4 hrs"]];

    /** @var list<array{string, string}> */
    public static array $date_accuracy_select = [["-1","Default"],["0","Exact"],["1","Day"],["2","Month"],["3","Season"],["4","Year"]];

    /** @var list<array{string, string}> */
    public static array $bool_select = [["1","True"],["0","False"]];

    /** @var array<string, string> */
    public static array $us_states = [];

    /** @var array<string, string> */
    public static array $countries = [];

    public static function init(): void
    {
        if (!empty(self::$countries)) {
            return;
        }
        $dataFile = dirname(__DIR__, 2) . '/data/geo.php';
        if (file_exists($dataFile)) {
            $data = require $dataFile;
            self::$us_states = $data['us_states'];
            self::$countries = $data['countries'];
        }
    }
}
