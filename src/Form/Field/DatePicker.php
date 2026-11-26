<?php

namespace ExmentAdminCore\Admin\Form\Field;

/**
 * The date and time pickers on flatpickr (2.0). They were eonasdan's bootstrap-datetimepicker
 * on moment (Bootstrap 3).
 *
 * The fields and filters keep taking the options of the old picker, written as moment formats
 * ('YYYY-MM-DD HH:mm:ss'): they are turned into the flatpickr ones here. The options flatpickr
 * has no use for (icons, widgetParent, useCurrent...) are dropped.
 */
class DatePicker
{
    /**
     * moment format tokens and the flatpickr ones (the longest first).
     *
     * @var array<string, string>
     */
    public const FORMAT_TOKENS = [
        'YYYY' => 'Y',
        'YY' => 'y',
        'MMMM' => 'F',
        'MMM' => 'M',
        'MM' => 'm',
        'M' => 'n',
        'dddd' => 'l',
        'ddd' => 'D',
        'DD' => 'd',
        'D' => 'j',
        'HH' => 'H',
        'H' => 'H',
        'hh' => 'G',
        'h' => 'h',
        'mm' => 'i',
        'm' => 'i',
        'ss' => 'S',
        's' => 's',
        'A' => 'K',
        'a' => 'K',
        'X' => 'U',
    ];

    /**
     * The options of the old picker that mean the same in flatpickr.
     *
     * @var array<int, string>
     */
    public const KEPT_OPTIONS = [
        'minDate', 'maxDate', 'defaultDate', 'enable', 'disable', 'weekNumbers', 'time_24hr',
        'enableTime', 'enableSeconds', 'noCalendar', 'allowInput', 'altInput', 'altFormat',
        'minuteIncrement', 'hourIncrement', 'defaultHour', 'defaultMinute', 'defaultSeconds',
        'inline', 'static', 'position', 'mode', 'clickOpens', 'disableMobile', 'wrap',
    ];

    /**
     * A moment format as a flatpickr one: 'YYYY-MM-DD HH:mm:ss' → 'Y-m-d H:i:S'.
     * The text in [brackets] is kept as it is.
     */
    public static function format(string $moment): string
    {
        $tokens = implode('|', array_map(function ($token) {
            return preg_quote($token, '/');
        }, array_keys(static::FORMAT_TOKENS)));

        return strval(preg_replace_callback('/\[([^\]]*)\]|' . $tokens . '|([a-zA-Z])/', function ($m) {
            if (isset($m[1]) && $m[1] !== '' && $m[0][0] === '[') {
                // a literal: every letter escaped, so flatpickr does not read it as a token
                return preg_replace('/([a-zA-Z])/', '\\\\$1', $m[1]);
            }
            if ($m[0] === '[]') {
                return '';
            }
            if (isset(static::FORMAT_TOKENS[$m[0]])) {
                return static::FORMAT_TOKENS[$m[0]];
            }
            // a letter that is no moment token: kept, escaped
            return '\\' . $m[0];
        }, $moment));
    }

    /**
     * The flatpickr options of the options of a field.
     *
     * @param array<string, mixed> $options the options of the field (format as moment)
     * @param array<string, mixed> $defaults
     *
     * @return array<string, mixed>
     */
    public static function options(array $options, string $defaultFormat = 'YYYY-MM-DD', array $defaults = []): array
    {
        $format = strval($options['format'] ?? '') ?: $defaultFormat;

        $config = [];
        foreach (array_merge($defaults, $options) as $key => $value) {
            if (in_array($key, static::KEPT_OPTIONS, true)) {
                $config[$key] = $value;
            }
        }

        $config['dateFormat'] = static::format($format);
        $config['allowInput'] = $config['allowInput'] ?? true;
        $config['time_24hr'] = $config['time_24hr'] ?? !preg_match('/[hA]|(?<![a-zA-Z])a/', $format);

        // what the format shows decides what can be picked
        $hasDate = (bool) preg_match('/Y|M|D|X/', $format);
        $hasTime = (bool) preg_match('/[Hhm]/', preg_replace('/MM?M?M?/', '', $format) ?? '');
        $config['enableTime'] = $hasTime;
        $config['enableSeconds'] = $hasTime && str_contains($format, 's');
        $config['noCalendar'] = !$hasDate;
        if (!$hasDate) {
            // a time alone starts at 00:00:00 (the old field did the same), not at noon
            $config += ['defaultHour' => 0, 'defaultMinute' => 0];
        }
        if (!$hasTime) {
            unset($config['enableSeconds']);
        }

        return $config;
    }

    /**
     * The script that puts a picker on the inputs of a selector (once an input).
     *
     * @param string $selector a CSS selector
     * @param array<string, mixed> $config flatpickr options
     * @param string|null $locale the app locale; null: config('app.locale')
     */
    public static function script(string $selector, array $config, ?string $locale = null): string
    {
        return '(function () {' . static::body($selector, $config, $locale) . '})();';
    }

    /**
     * Two pickers, a start and an end: the end cannot be before the start.
     *
     * @param array<string, mixed> $config flatpickr options
     */
    public static function rangeScript(string $startSelector, string $endSelector, array $config, ?string $locale = null): string
    {
        return '(function () {'
            . static::body($startSelector, $config, $locale, 'start')
            . static::body($endSelector, $config, $locale, 'end')
            . 'if (!start || !end) { return; }'
            . 'start.config.onChange.push(function (dates, text) { end.set("minDate", text || null); });'
            . 'end.config.onChange.push(function (dates, text) { start.set("maxDate", text || null); });'
            . '})();';
    }

    /**
     * @param array<string, mixed> $config
     */
    protected static function body(string $selector, array $config, ?string $locale, ?string $var = null): string
    {
        $locale = strtolower(str_replace('_', '-', $locale ?? strval(config('app.locale'))));
        $json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS) ?: '{}';
        $selectorJson = json_encode($selector, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS) ?: '""';
        $localeJson = json_encode($locale, JSON_HEX_TAG | JSON_HEX_APOS) ?: '""';
        $assign = isset($var) ? "{$var} = el._flatpickr;" : '';

        return ($var ? "var {$var} = null;" : '')
            . 'if (typeof window.flatpickr !== "function") { return; }'
            . "var c = {$json}, l = {$localeJson}, L = window.flatpickr.l10ns || {};"
            // the l10n file of the app locale is loaded with the base assets: ja, zh-cn → zh
            . 'if (L[l]) { c.locale = l; } else if (L[l.split("-")[0]]) { c.locale = l.split("-")[0]; }'
            . "document.querySelectorAll({$selectorJson}).forEach(function (el) {"
            . 'if (!el._flatpickr) { window.flatpickr(el, c); }'
            . $assign
            . '});';
    }
}
