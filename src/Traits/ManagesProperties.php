<?php
  namespace Saf\DateRangePicker\Traits;

  trait ManagesProperties
  {
    public $inputAttributes = [];
    public $picker = 'single'; //single, range
    public $multiSelect = false; //single, range
    public $initialMonthYear = '';
    public $defaultHoverText = '';
    public $preOccupiedDates = [];
    public $selectedDates = [];
    public $disableDates = [];
  }
  