
# Laravel Date Range Picker

A Livewire-powered date range picker component for Laravel applications, offering single- and multi-range selection, customizable hover data, dark mode, and easy formatting.

## Table of Contents

- [Requirements](#requirements)  
- [Installation](#installation)  
- [Publishing Assets & Stubs](#publishing-assets--stubs)  
- [Usage](#usage)  
  - [Blade Integration](#blade-integration)  
  - [Customization via Subclassing](#customization-via-subclassing)  
  - [Generating New Pickers](#generating-new-pickers)  
  - [Dark Mode](#dark-mode)  
- [Facade & Formatting](#facade--formatting)
- [Troubleshooting](#troubleshooting)  
- [License](#license)

---

## Requirements

- PHP 8.2+  
- Laravel 10+  
- Livewire 3+  

---

## Installation

Require the package via Composer:

```bash
composer require saf/date-range-picker
```
The package’s service provider and facade alias will be auto-discovered thanks to PSR-4 & Laravel’s package discovery.


## Publishing Assets & Stubs

To customize views, public assets, or the stub used by the `saf:make-daterange-picker` command, publish them:
```bash
# Publish views
php artisan vendor:publish \
  --provider="Saf\DateRangePicker\DateRangePickerServiceProvider" \
  --tag=views
```
This will copy:

-   **Views** → `resources/views/vendor/saf-date-range-picker`
    
    
## Usage

### Blade Integration
```bash
@livewireStyles

@livewire('saf-date-range-picker', [
    'picker'           => 'range',         // 'single' or 'range'
    'multiSelect'      => true,            // allow multiple ranges
    'initialMonthYear' => now()->format('Y-m'),
    'preOccupiedDates' => [
        ['startDate' => '2025-06-01', 'endDate' => '2025-06-05'],
    ],
    'disableDates'     => [
        ['startDate' => '2025-06-10'],
    ],
    'darkMode'         => false,
])

@livewireScripts

```
**Note:** The input field will bind to either `selectedDates` (single mode) or `selectedRanges` (range mode).

### Customization via Subclassing

Extend the base picker to override methods like `getLivewireHoverData()`:
```bash
namespace App\Livewire;

use Saf\DateRangePicker\Livewire\DateRangePicker as BasePicker;

class MyCustomPicker extends BasePicker
{
    public function getLivewireHoverData($currentMonthYear)
    {
        return [
            1 => "First Day Hover Data for {$currentMonthYear}",
            2 => "Second Day Hover Data for {$currentMonthYear}",
        ];
    }
}
```
Then in Blade:
```bash
@livewire('my-custom-picker', [
    'picker'           => 'range',
    'multiSelect'      => true,
    'initialMonthYear' => now()->format('Y-m'),
    'preOccupiedDates' => [
        ['startDate' => '2025-06-01', 'endDate' => '2025-06-05'],
    ],
    'disableDates'     => [
        ['startDate' => '2025-06-10'],
    ],
    'darkMode'         => false,
])

```
### Generating New Pickers

Use the `saf:make-daterange-picker` Artisan command:
```bash
php artisan saf:make-daterange-picker ReportDatePicker
```
This will scaffold a new class in `app/Livewire/ReportDatePicker.php` based on the published stub.  
By editing `/stubs/date-range-picker.stub` you can inject default methods (e.g., a pre-defined `getLivewireHoverData()`) into every newly generated picker.
### Dark Mode

Toggle dark styling by passing `darkMode => true`:
```bash
@livewire('saf-date-range-picker', [
    'darkMode' => true,
])

```
Or control it dynamically by binding a boolean property from your parent component.
## Facade & Formatting

Use the `DateRangeFormatter` facade to normalize user input:
```bash
use DateRangeFormatter;

$raw  = "2025-06-01,2025-06-05";
$norm = DateRangeFormatter::format($raw);
// => "[2025-06-01, 2025-06-05]"

```
In a controller:
```bash
public function store(Request $request)
{
    $value = DateRangeFormatter::format($request->input('date_range'));
    // Persist or process…
}
```
Alternatively:

-   Resolve via container: `app('saf-date-range-formatter')->format($raw)`
    
-   Inject `Saf\DateRangePicker\Services\DateRangeFormatterService` in method signature.
## Troubleshooting

-   **Alpine `$wire` undefined:** Ensure you load `@livewireScripts` after any Alpine includes, and do not include Alpine twice.
    
-   **Multiple Alpine instances:** Remove duplicate Alpine `<script>` tags.
    
-   **Stub changes not applied:** Clear `bootstrap/cache` or restart your server.
-    **Make sure Alpine isn't already installed**

If the application you are using already has AlpineJS installed, you will need to remove it for Livewire to work properly; otherwise, Alpine will be loaded twice and Livewire won't function. For example, if you installed the Laravel Breeze "Blade with Alpine" starter kit, you will need to remove Alpine from  `resources/js/app.js`.

## License

The MIT License (MIT). See `LICENSE` for details