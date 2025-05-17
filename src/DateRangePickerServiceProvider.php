<?php
namespace Saf\DateRangePicker;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Saf\DateRangePicker\Livewire\DateRangePicker as Component;

class DateRangePickerServiceProvider extends ServiceProvider
{
    public function register()
    {
        // bind the formatter
        $this->app->singleton(
            'saf-date-range-formatter',
            \Saf\DateRangePicker\Services\DateRangeFormatterService::class
        );
        
        // Register Artisan command
        $this->commands([
            \Saf\DateRangePicker\Console\Commands\MakeDateRangePickerCommand::class,
        ]);
    }

    public function boot()
    {
        // 1) Make your package views available under the 'saf-date-range-picker' namespace:
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'saf-date-range-picker');

        // 2) Publishable resources:
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/saf-date-range-picker'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../stubs/date-range-picker.stub' => base_path('stubs/date-range-picker.stub'),
        ], 'stubs');

        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/saf-date-range-picker'),
        ], 'assets');

        // 3) Register Livewire component
        Livewire::component('saf-date-range-picker', Component::class);
    }
}
