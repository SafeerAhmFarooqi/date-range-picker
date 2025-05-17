<?php
namespace Saf\DateRangePicker\Facades;

use Illuminate\Support\Facades\Facade;

class DateRangeFormatter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'saf-date-range-formatter';
    }
}