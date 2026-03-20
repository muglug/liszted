<?php

declare(strict_types=1);

namespace Liszted\Controller;

class UserSession
{
    public static ?int $id = null;

    public static function fetch(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        self::$id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function set(int $id): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $id;
        self::$id = $id;
    }

    public static function signout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user_id']);
        self::$id = null;
        session_destroy();
    }
}
