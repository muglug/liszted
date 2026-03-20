<?php

declare(strict_types=1);

namespace Liszted\Controller;

class Form
{
    public static string $field_row = "";

    public static function flush(): void
    {
        self::$field_row = "";
    }

    public static function fieldRow(): string
    {
        $r = self::$field_row;
        self::$field_row = "";
        return "<div class=\"field-row\">{$r}</div>";
    }

    public static function textInput(string $id, string $value = "", string $label = "", string $class = ""): string
    {
        $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $input = "<input type=\"text\" value=\"{$escapedValue}\" name=\"{$id}\" id=\"{$id}\"";
        if (!empty($class)) {
            $input .= " class=\"{$class}\"";
        }
        if (!empty($label)) {
            $input .= " placeholder=\"{$escapedLabel}\"";
        }
        $input .= "/>";
        self::$field_row .= $input;
        return $input;
    }

    public static function textArea(string $id, string $value = "", string $label = "", string $class = ""): string
    {
        $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $input = "<textarea name=\"{$id}\" id=\"{$id}\"";
        if (!empty($class)) {
            $input .= " class=\"{$class}\"";
        }
        if (!empty($label)) {
            $input .= " placeholder=\"{$escapedLabel}\"";
        }
        $input .= ">{$escapedValue}</textarea>";
        self::$field_row .= $input;
        return $input;
    }

    public static function password(string $id, string $value = "", string $label = "", string $class = ""): string
    {
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $input = "<input type=\"password\" value=\"\" name=\"{$id}\" id=\"{$id}\"";
        if (!empty($class)) {
            $input .= " class=\"{$class}\"";
        }
        if (!empty($label)) {
            $input .= " placeholder=\"{$escapedLabel}\"";
        }
        $input .= "/>";
        self::$field_row .= $input;
        return $input;
    }

    public static function label(string $id, string $text, string $class = ""): string
    {
        $escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $label = "<label for=\"{$id}\">{$escapedText}</label>";
        self::$field_row .= $label;
        return $label;
    }

    public static function hiddenInput(string $id, string $value = "", string $class = ""): string
    {
        $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $input = "<input type=\"hidden\" value=\"{$escapedValue}\" name=\"{$id}\" id=\"{$id}\"";
        if (!empty($class)) {
            $input .= " class=\"{$class}\"";
        }
        $input .= "/>";
        self::$field_row .= $input;
        return $input;
    }

    public static function checkbox(string $id, bool $checked = false, string $class = ""): string
    {
        $input = "<input type=\"checkbox\" name=\"{$id}\" id=\"{$id}\"";
        if ($checked) {
            $input .= " checked=\"checked\"";
        }
        if (!empty($class)) {
            $input .= " class=\"{$class}\"";
        }
        $input .= "/>";
        self::$field_row .= $input;
        return $input;
    }

    /**
     * @param array<int|string, mixed> $options
     */
    public static function select(string $id, string $value = "", array $options = [], string $label = "", string $class = ""): string
    {
        $select = "<select class=\"{$class}\" name=\"{$id}\" id=\"{$id}\">";
        if (!empty($label)) {
            $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $select .= "<option disabled=\"disabled\">{$escapedLabel}</option>";
        }
        foreach ($options as $k => $o) {
            if (!is_array($o)) {
                $o = [!is_int($k) ? $k : $o, $o];
            }
            $optValue = htmlspecialchars((string) $o[0], ENT_QUOTES, 'UTF-8');
            $optLabel = htmlspecialchars((string) $o[1], ENT_QUOTES, 'UTF-8');
            $selected = ($value == (string) $o[0]) ? " selected=\"selected\"" : "";
            $select .= "<option value=\"{$optValue}\"{$selected}>{$optLabel}</option>";
        }
        $select .= "</select>";
        self::$field_row .= $select;
        return $select;
    }

    /**
     * @param array<string, string> $attributes
     */
    public static function button(string $id, string $value = "", string $class = "", array $attributes = []): string
    {
        $button = "<button id=\"{$id}\"";
        if (empty($attributes) || !isset($attributes['type'])) {
            $button .= " type=\"button\"";
        }
        if (!empty($class)) {
            $button .= " class=\"{$class}\"";
        }
        foreach ($attributes as $k => $v) {
            $escapedV = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            $button .= " {$k}=\"{$escapedV}\"";
        }
        $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $button .= "><div class=\"ic\"></div><span>{$escapedValue}</span></button>";
        self::$field_row .= $button;
        return $button;
    }
}
