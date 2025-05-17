<?php

namespace Saf\DateRangePicker\Traits;

use DateTime;

/**
 * Trait HandlesDates
 * Provides functionality for initializing and formatting date ranges and month-year values.
 */
trait HandlesDates
{
    /**
     * Initialize the date-related properties.
     *
     * This includes:
     * - Validating and formatting the initial month-year.
     * - Formatting and zero-padding the date ranges for pre-occupied and disabled dates.
     *
     * @return void
     */
    public function initializeDates(): void
    {
        // Initialize the initial month-year format
        $this->initializeInitialMonthYear();

        // Validate and format pre-occupied dates
        $this->preOccupiedDates = $this->validateAndFormatDateRange($this->preOccupiedDates);

        // Validate and format disabled dates
        $this->disableDates = $this->validateAndFormatDateRange($this->disableDates);
    }

    public function clearSelection(){
        $this->selectedDates = [];
    }

    /**
     * Validate and format the initialMonthYear property.
     *
     * Ensures the format is Y-m, validates the year and month, and defaults
     * to the current year and month if the input is invalid.
     *
     * @return void
     */
    public function initializeInitialMonthYear(): void
    {
        // Regex to match the expected format YYYY-MM
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $this->initialMonthYear, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];

            // Validate the year range
            if ($year < 1900 || $year > 2300) {
                $year = now()->year; // Default to the current year
            }

            // Validate the month range
            if ($month < 1 || $month > 12) {
                $month = 1; // Default to January
            }

            // Format the initialMonthYear as YYYY-MM
            $this->initialMonthYear = sprintf('%04d-%02d', $year, $month);
        } else {
            // Default to the current year and month if the format is invalid
            $this->initialMonthYear = now()->format('Y-m');
        }
    }

    /**
     * Validate, format, and zero-pad a range of dates.
     *
     * Ensures each range contains a valid startDate and optionally an endDate,
     * formatted as YYYY-MM-DD. If startDate is invalid, the entire range is neglected.
     *
     * @param array $ranges An array of date ranges, each containing startDate and endDate.
     * @return array Validated and formatted array of date ranges.
     */
    public function validateAndFormatDateRange(array $ranges): array
    {
        return array_filter(array_map(function ($range) {
            // Zero-pad and validate the start date
            $startDate = $this->zeroPadDate($range['startDate'] ?? null);
            if (!$this->isValidDate($startDate)) {
                return null; // Neglect the range if startDate is invalid or missing
            }
    
            // Zero-pad and validate the end date if provided, otherwise default to startDate
            $endDate = isset($range['endDate'])
                ? $this->zeroPadDate($range['endDate'])
                : $startDate;
    
            if ($endDate && !$this->isValidDate($endDate)) {
                $endDate = $startDate; // Default to startDate if endDate is invalid
            }
    
            return [
                'startDate' => $startDate,
                'endDate' => $endDate,
            ];
        }, $ranges));
    }

   /**
   * Zero-pad a given date.
   *
   * This method ensures the date parts (year, month, day) are zero-padded
   * to conform to the Y-m-d format before validation.
   *
   * @param string|null $date The date to format and zero-pad.
   * @return string|null Zero-padded date in Y-m-d format, or null if invalid format.
   */
  private function zeroPadDate(?string $date): ?string
  {
      if (!$date) {
          return null; // Null or empty input remains null
      }

      // Try to split the date into parts
      $parts = explode('-', $date);
      if (count($parts) !== 3) {
          return null; // Return null if not in the expected "Y-m-d" format
      }

      [$year, $month, $day] = $parts;

      // Zero-pad each part and return the formatted date
      return sprintf('%04d-%02d-%02d', $year, $month, $day);
  }

    /**
     * Validate if a given date is in the correct format and within the valid range.
     *
     * @param string|null $date The date to validate.
     * @return bool True if the date is valid, false otherwise.
     */
    private function isValidDate(?string $date): bool
    {
        if (!$date) {
            return false; // Null or empty date is invalid
        }

        $parsedDate = DateTime::createFromFormat('Y-m-d', $date);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            return false; // Invalid format
        }

        $year = (int)$parsedDate->format('Y');
        if ($year < 1900 || $year > 2300) {
            return false; // Year out of range
        }

        return true; // Date is valid
    }
}
