<?php

declare(strict_types=1);

namespace Keboola\S3Writer;

class PrefixProcessor
{
    /**
     * @var string[]
     */
    private static array $supportedTemplates = [
        '{timestamp}' => 'YmdHis',
        '{date}' => 'Ymd',
        '{time}' => 'His',
    ];

    public static function process(string $prefix): string
    {
        foreach (self::$supportedTemplates as $replacement => $dateFormat) {
            if (str_contains($prefix, $replacement)) {
                return str_replace($replacement, date($dateFormat), $prefix);
            }
        }

        return $prefix;
    }
}
