<?php
namespace Saf\DateRangePicker\Livewire;

use Livewire\Component;
use Saf\DateRangePicker\Traits\Initializer;
use Saf\DateRangePicker\Traits\ManagesProperties;
use Saf\DateRangePicker\Traits\HandlesDates;

class DateRangePicker extends Component
{
    use Initializer, ManagesProperties, HandlesDates;

    public $darkMode = false;

    public function mount()
    {
        $this->initialize();
    }

    public function getLivewireHoverData($currentMonthYear)
    {
        return [
            1 => "First Day Hover Data for {$currentMonthYear}",
            2 => "Second Day Hover Data for {$currentMonthYear}",
        ];
    }

    public function render()
    {
        return view('saf-date-range-picker::livewire.date-range-picker');
    }
}
