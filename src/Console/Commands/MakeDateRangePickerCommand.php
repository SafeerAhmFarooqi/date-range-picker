<?php
namespace Saf\DateRangePicker\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeDateRangePickerCommand extends GeneratorCommand
{
    protected $signature = 'saf:make-daterange-picker {name}';
    protected $description = 'Scaffold a Livewire DateRangePicker subclass';
    protected $type = 'Livewire DateRangePicker';

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/date-range-picker.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return config('livewire.class_namespace', $rootNamespace.'\Livewire');
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of your new picker class'],
        ];
    }

    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);
        $full = $this->qualifyClass($name);
        $ns   = trim(implode('\\', array_slice(explode('\\', $full), 0, -1)), '\\');
        return str_replace('DummyNamespace', $ns, $stub);
    }
}
