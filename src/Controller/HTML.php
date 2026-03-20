<?php

declare(strict_types=1);

namespace Liszted\Controller;

class HTML
{
    public static function head(string $title): string
    {
        $escapedTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return <<<EOT
        <!DOCTYPE html>
        <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Liszted - {$escapedTitle}</title>
        <link href="/css/global.css" media="screen" type="text/css" rel="stylesheet" />
        <link href="/css/form.css" media="screen" type="text/css" rel="stylesheet" />
        <link href="/css/dialog.css" media="screen" type="text/css" rel="stylesheet" />
        <link href="/css/list.css" media="screen" type="text/css" rel="stylesheet" />
        <link href="/css/login.css" media="screen" type="text/css" rel="stylesheet" />
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="/js/jquery-ui-1.8.16.min.js"></script>
        </head>
        EOT;
    }
}
