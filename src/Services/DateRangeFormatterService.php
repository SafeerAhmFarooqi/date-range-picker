<?php
namespace Saf\DateRangePicker\Services;

use Saf\DateRangePicker\Traits\DateRangeFormatter as FormatterTrait;

class DateRangeFormatterService
{
    use FormatterTrait;

    /**
     * A friendly facade entry-point.
    */
    public function format(string $rawValue): string
    {
        return $this->formatDateRange($rawValue);
    }
}