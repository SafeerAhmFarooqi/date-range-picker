<?php

namespace Saf\DateRangePicker\Traits;

trait DateRangeFormatter
{
    /**
     * Format date range input into standardized bracketed string.
     *
     * Scenarios:
     * - Single date (no brackets, no comma)        => [date]
     * - Multiple dates (no brackets, comma list)   => [date1, date2, ...]
     * - Single range (one bracketed list)          => [[date1, date2, ...]]
     * - Multiple ranges (comma list of brackets)    => [[range1], [range2], ...]
     *
     * @param string $rawValue
     * @return string
     * @throws \InvalidArgumentException
     */
    public function formatDateRange($rawValue)
    {
        $value = trim($rawValue);

        if ($value === '') {
            throw new \InvalidArgumentException('Empty date range provided');
        }

        // Multi-range multi-select: detect '], [' pattern
        if (strpos($value, '], [') !== false) {
            $trimmed = trim($value, '[]');
            $groups  = preg_split('/\],\s*\[/', $trimmed);
            $ranges  = array_map(function ($group) {
                return '[' . trim($group, ' []') . ']';
            }, $groups);

            return '[' . implode(', ', $ranges) . ']';
        }

        // Single bracketed group (single range or single date)
        if (substr($value, 0, 1) === '[' && substr($value, -1) === ']') {
            $inner = trim($value, '[]');
            // If only one date inside brackets
            if (strpos($inner, ',') === false) {
                return '[' . $inner . ']';
            }
            // Range of multiple dates
            return '[[' . $inner . ']]';
        }

        // No brackets: split by comma to detect single vs multi select
        $parts = array_map('trim', explode(',', $value));
        if (count($parts) === 1) {
            // Single date
            return '[' . $parts[0] . ']';
        }
        // Multiple dates
        return '[' . implode(', ', $parts) . ']';
    }
}
